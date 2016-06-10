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

/**
* @global bool $GLOBALS['_csv_title_output']
* @name $_csv_title_output_flag
* true=表示、false=非表示
*/
$GLOBALS['_csv_title_output_flag'] = true;
/**
* @global array $GLOBALS['_csv_data_output_list']
* @name $_csv_data_output_list
*/
$GLOBALS['_csv_data_output_list'] = array();
/**
* @global array $GLOBALS['_csv_file_name']
* @name $_csv_file_name
* {YYYY_MM_DD_HHMMSS}.csv
*/
$GLOBALS['_csv_file_name'] = date("Y_m_d_His") . '.csv';

//
try{
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
        //
        $set_id_index = 1;
        $set_id = explode('_', $target_list_value)[$set_id_index];
        //
        $csv_title_flag = false;

        /**
        * group_idからdocument_idを取り出す
        */
        $document_id_index = 0;
        $document_id = explode('_', $target_list_value)[$document_id_index];

        /**
        * csvファイルパスを生成 & csvフォーマットを取得
        */
        $csv_out_path = CSV_ROOT_PATH . $target_list_key . RELATIVE_PATH . $document_id . '/';
        create_file_path($csv_out_path, 0777);
        //csvファイル名を生成
        $csv_output_file = $csv_out_path . $_csv_file_name;
        if (!file_exists ($csv_output_file)){
          //csv表題を取得
          $sql_target = "SELECT entry_id, value
                         FROM {$csv_format_tabel}
                         WHERE organization_id = '{$target_list_key}'
                         AND document_id = '{$document_id}'
                         ORDER BY entry_id";
          // SQL処理を実行
          $stt_csv_title_list = sql_execute($dbh, $sql_target);
          //csvフォーマットをメモリへ読み込む
          $csv_format_title = null;
          while ($row_csv_title = $stt_csv_title_list->fetch(PDO::FETCH_ASSOC)) {
            $csv_format_title[] = $row_csv_title['value'];
          }
          //エンコードを変換
          mb_convert_variables('sjis-win','UTF-8',$csv_format_title);
          //
          $csv_title_flag = true;
          $stt_csv_title_list = null;
        }

        /**
        * 処理対象内容を復号化し、csv出力リストへ入れる
        */
        //処理対象クエリ文字列を作成
        $sql_target = "SELECT entry_id, result, parts_d_code
                       FROM {$file_parts_table}
                       WHERE organization_id = '{$target_list_key}'
                       AND set_id = '{$set_id}'
                       ORDER BY entry_id";
        // SQL処理を実行
        $stt_target_data_list = sql_execute($dbh, $sql_target);
        //クエリ処理を実行
        $_csv_data_output_list = null;
        while ($row_csv_data = $stt_target_data_list->fetch(PDO::FETCH_ASSOC)) {
          /**
          * 復号化を実施
          */
          $post_data = array(
            'parts_d_code' => $row_csv_data['parts_d_code'],
            'object' => $row_csv_data['result']
          );
          $decrypted_result = decode_object($post_data);

          /*↓↓↓ [start]バリエーションを実施<未実装> ↓↓↓*
          /*↑↑↑ [end]バリエーションを実施<未実装> ↑↑↑*/

          //resultを設定
          $_csv_data_output_list[$target_list_key][$target_list_value][$row_csv_data['entry_id']] =
            mb_convert_encoding($decrypted_result, 'sjis-win', 'UTF-8');
        }

        /**
        * csvファイルへ出力
        */
        output_csv($csv_output_file, $_csv_title_output_flag, $csv_title_flag,
                   $csv_format_title, $_csv_data_output_list);

        /**
        * csv生成が完了したら、DBのstatusを更新
        */
        try {
          //トランザクションを開始
          $dbh->beginTransaction();
          foreach ($target_list as $csv_key => $tmp_value){
            foreach ($tmp_value as $tmp_key => $csv_value) {
              //処理対象クエリ文字列を作成
              $update_sql = "UPDATE {$file_parts_table}
                             SET status = {$processed}
                             WHERE organization_id = '{$csv_key}'
                             AND set_id = '{$set_id}'";
              //DB更新を実施
              $dbh->exec($update_sql);
            }
          }
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
