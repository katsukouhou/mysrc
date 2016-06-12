<?php
/**
* 性能テスト用のソースファイルの読み込み
*/
require_once './include/Utility.php';
memory_usage_start();
pro_start_time();

/**
* DB接続を行うソースファイルの読み込み
*/
require_once './include/DBManager.php';
/**
* CSV出力を行うソースファイルの読み込み
*/
require_once './include/CSVManager.php';
/**
* 復号化を行うソースファイルの読み込み
*/
require_once './include/Decryption.php';

/**
* TimeZoneを設定
*/
date_default_timezone_set('Asia/Tokyo');
/**
* スクリプトの実行制限時間を設定
*/
set_time_limit(0);

//
try{
  //CSVManagerを生成
  $csv_manager = new CSVManager();
  //DecryptionManagerを生成
  $decryption_manager = new Decryption();

  //DBを接続
  $dbh = db_connect();
  //処理対象クエリ文字列を作成
  $sql_target = "SELECT DISTINCT organization_id, paper_id, set_id
                 FROM {$file_parts_table}
                 WHERE status = {$unprocessed}";
  // SQL処理を実行
  $stt_target_list = sql_execute($dbh, $sql_target);
  $query_num = $stt_target_list->rowCount();
  if($query_num != 0) {
    /**
    * 処理対象リストを作成
    */
    while ($row = $stt_target_list->fetch(PDO::FETCH_ASSOC)) {
      $document_id_index = 0;
      $document_id = explode('_', $row['paper_id'])[$document_id_index];
      $target_list[$row['organization_id']][] = $document_id . '_' . $row['set_id'];
    }
    //
    $stt_target_list = null;

    /**
    * 処理対象データをDB file_partsからメモリへ読み込み、csvファイルへ出力
    */
    foreach ($target_list as $target_list_key => $tmp_value) {
      foreach ($tmp_value as $tmp_key => $target_list_value) {
        /**
        * set_idを取り出す
        */
        $set_id_index = 1;
        $set_id = explode('_', $target_list_value)[$set_id_index];

        /**
        * group_idからdocument_idを取り出す
        */
        $document_id_index = 0;
        $document_id = explode('_', $target_list_value)[$document_id_index];

        /**
        * csvファイルパスを生成
        */
        $csv_manager->create_file_path($target_list_key, $document_id);

        /**
        * 処理対象内容を復号化し、csv出力リストへ入れる
        */
        //処理対象クエリ文字列を作成
        $sql_target = "SELECT entry_id, parts_d_code, item_name, type
                       FROM {$file_parts_table}
                       WHERE organization_id = '{$target_list_key}'
                       AND set_id = '{$set_id}'
                       ORDER BY entry_id";
        //SQL処理を実行
        $stt_target_data_list = sql_execute($dbh, $sql_target);
        //csvデータを生成
        $csv_manager->csv_array = null;
        $csv_format_title = array();
        $update_status_sql = '';
        $update_price_sql = '';
        $update_character_aggregation_sql = '';
        while ($row_csv_data = $stt_target_data_list->fetch(PDO::FETCH_ASSOC)) {
          //csvフォーマットを取得
          $csv_format_title[] = $row_csv_data['item_name'];
          //復号化を実施
          $post_data = array(
            'parts_d_code' => $row_csv_data['parts_d_code']
          );
          $decrypted_result = $decryption_manager->decode_object($post_data);

          //バリエーション処理を実施
          /*↓↓↓ [start]バリエーションを実施<未実装> ↓↓↓*/
          /*↑↑↑ [end]バリエーションを実施<未実装> ↑↑↑*/

          //resultを設定
          $csv_manager->csv_array[$target_list_key][$target_list_value][$row_csv_data['entry_id']] =
            $decrypted_result;

          //priceを算出し
          //$target_price = cal_price($decrypted_result, $row_csv_data['type']);
          $target_price = cal_price_without_type($decrypted_result);
          //price更新用のSQL文を生成
          $update_price_sql .=
                        "UPDATE {$file_parts_table}
                         SET price = {$target_price}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";

          //status更新用のSQL文を生成
          $update_status_sql .=
                        "UPDATE {$file_parts_table}
                         SET status = {$processed}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";

          //不読と可読の文字数を算出
          list($read_count, $unread_count) = aggregate_characters($decrypted_result);
          //不読と可読の文字数の更新用のSQLを生成
          $update_character_aggregation_sql .=
                        "UPDATE {$file_parts_table}
                         SET read_count = {$read_count}, unread_count = {$unread_count}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";
        }

        /**
        * csvファイルへ出力
        */
        $csv_manager->csv_title = $csv_format_title;
        $csv_manager->output_csv();

        /**
        * csv生成が完了したら、DBを更新
        */
        try {
          //トランザクションを開始
          $dbh->beginTransaction();
          //statusを更新
          //$dbh->exec($update_status_sql);
          //priceを更新
          $dbh->exec($update_price_sql);
          //不読と可読の文字数を更新
          $dbh->exec($update_character_aggregation_sql);
          //トランザクションをコミット
          $dbh->commit();
        } catch (Exception $e) {
          //トランザクションをロールバック
          $dbh->rollBack();
          print('DB update Failed:' . $e->getMessage());
        }//try & catch

      }//foreach $tmp_value

    }//foreach $target_list

  }
} catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}//try & catch

//
$dbh = null;

//性能結果を表示
memory_usage_end();
pro_end_time();

?>
