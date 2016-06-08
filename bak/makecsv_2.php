<?php
/*****-- 性能テスト用のソースファイルの読み込み --*****/
require_once './include/Utility.php';
memory_usage_start();
pro_start_time();

/*****-- DB接続を行うソースファイルの読み込み --*****/
require_once './include/DBManager.php';

/*****-- カスタマイズ項目 --*****/
date_default_timezone_set('Asia/Tokyo');
set_time_limit(0);
$visible_format = true;//true=表示、false=非表示
$csv_data_output_list = null;

//
try{
  /* 処理対象リストを作成 */
  //DBを接続
  $dbh = connect();
  //処理対象クエリ文字列を作成
  $sql_target = "SELECT DISTINCT organization_id, group_id
                 FROM {$file_parts_table}
                 WHERE status = {$unprocessed}";
  //プリペアドステートメントを生成
  $stt_target_list = $dbh->prepare($sql_target);
  //プリペアドステートメントを実行
  $stt_target_list->execute();
  //結果セットからレコードのデータをフェッチ
  $query_num = $stt_target_list->rowCount();
  if($query_num != 0) {
    //処理対象リストを作成
    while ($row = $stt_target_list->fetch(PDO::FETCH_ASSOC)) {
      $target_list[$row['organization_id']][] = $row['group_id'];
    }
    //
    $stt_target_list = null;

    //データをDB file_partsからメモリへ読み込む
    foreach ($target_list as $target_list_key => $tmp_value) {
      foreach ($tmp_value as $tmp_key => $target_list_value) {
        //
        $csv_title_output_flag = false;
        //group_idからdocument_idを取り出す
        $document_id_index = 0;
        $document_id = explode('_', $target_list_value)[$document_id_index];
        //csvファイルパスを生成
        $csv_out_path = "/Applications/MAMP/htdocs/ie/home/paid_accounts/{$target_list_key}/vpdm/csv/{$document_id}/";
        if (!file_exists ($csv_out_path)){
          mkdir ("$csv_out_path", 0777, true);
        }
        //csvファイル名を生成
        $datetime = date("Y_m_d_His");//{YYYY_MM_DD_HHMMSS}.csv
        $csv_output_file = $csv_out_path . $datetime . '.csv';
        if (!file_exists ($csv_output_file)){
          //csv表題を取得
          $sql_target = "SELECT entry_id, value
                         FROM {$csv_format_tabel}
                         WHERE organization_id = '{$target_list_key}'
                         AND document_id = '{$document_id}'
                         ORDER BY entry_id";
          //プリペアドステートメントを生成
          $stt_csv_title_list = $dbh->prepare($sql_target);
          //プリペアドステートメントを実行
          $stt_csv_title_list->execute();
          //処理対象リストを作成
          $csv_format_title = null;
          while ($row_csv_title = $stt_csv_title_list->fetch(PDO::FETCH_ASSOC)) {
            $csv_format_title[] = $row_csv_title['value'];
          }
          //
          //$csv_data_output_list[$document_id]['format'] = $csv_format_title;
          mb_convert_variables('sjis-win','UTF-8',$csv_format_title);
          //
          $csv_title_output_flag = true;
          //
          $stt_csv_title_list = null;
        }

        //処理対象クエリ文字列を作成
        $sql_target = "SELECT entry_id, result, item_id
                       FROM {$file_parts_table}
                       WHERE organization_id = '{$target_list_key}'
                       AND group_id = '{$target_list_value}'
                       ORDER BY entry_id";
        //プリペアドステートメントを生成
        $stt_target_data_list = $dbh->prepare($sql_target);
        //プリペアドステートメントを実行
        $stt_target_data_list->execute();
        //クエリ処理を実行
        $csv_data_output_list = null;
        while ($row_csv_data = $stt_target_data_list->fetch(PDO::FETCH_ASSOC)) {
/*↓↓↓ [start]復号化を実施 <未実装> ↓↓↓*/
          $decrypted_result = $row_csv_data['result'];
/*↑↑↑ [end]復号化を実施 <未実装> ↑↑↑*/

/*↓↓↓ [start]バリエーションを実施<未実装> ↓↓↓*/
          //
          switch($row_csv_data['entry_id']){
            case 'G'://生年月日 形式：YYYY/MM/DD
/*↓↓↓ [start]code ↓↓↓*/
                //
                $validated_result = 'YYYY/MM/DD';
                //UpdateSQL文を生成
                $update_sql .= "UPDATE {$file_parts_table}
                                SET result = 'eeee'
                                WHERE entry_id = 'G';";
/*↑↑↑ [end]code ↑↑↑*/
                //必要であれば、ワーニングメッセージを設定
                $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                    = 'converted to YYYY/MM/DD';
                break;
            case 'M'://郵便番号 形式：
/*↓↓↓ [start]code ↓↓↓*/
                //
                $validated_result = 'xxx-xxxx';
                //UpdateSQL文を生成
                $update_sql .= "UPDATE {$file_parts_table}
                                SET result = 'ffff'
                                WHERE entry_id = 'M';";
/*↑↑↑ [end]code ↑↑↑*/
                //必要であれば、ワーニングメッセージを設定
                $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                    = 'converted to xxx-xxxx';
                break;
            case 'Y'://口座_ゆうちょ 形式：
/*↓↓↓ [start]code ↓↓↓*/
                //
                $validated_result = 'oooo';
                //UpdateSQL文を生成
/*↑↑↑ [end]code ↑↑↑*/
                //必要であれば、ワーニングメッセージを設定
                $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                    = 'converted to oooo';
                break;
            default:
                $validated_result = $decrypted_result;
          }
/*↑↑↑ [end]バリエーションを実施<未実装> ↑↑↑*/

          //resultを設定
          $csv_data_output_list[$target_list_key][$target_list_value][$row_csv_data['entry_id']] =
            mb_convert_encoding($decrypted_result, 'sjis-win', 'UTF-8');
        }

        //csvファイルを生成
        if ($csv_title_output_flag) {
          $fp = fopen($csv_output_file,'w');
          if ($visible_format) {
            //fputcsv($fp, $csv_data_output_list[$document_id]['format']);
            fputcsv($fp, $csv_format_title);
          }
        } else {
          $fp = fopen($csv_output_file,'a');
        }
        //csvファイルへ出力
        foreach ($csv_data_output_list as $csv_key => $csv_value){
          foreach ($csv_value as $key => $value) {
            fputcsv($fp, $csv_data_output_list[$csv_key][$key]);
          }
        }
        //csvファイルをクローズ
        fclose($fp);

        //csv生成が完了したら、DBのstatusを更新
        try {
          //トランザクションを開始
          $dbh->beginTransaction();
          foreach ($target_list as $csv_key => $tmp_value){
            foreach ($tmp_value as $tmp_key => $csv_value) {
              //処理対象クエリ文字列を作成
              $update_sql = "UPDATE {$file_parts_table}
                             SET status = {$processed}
                             WHERE organization_id = '{$csv_key}'
                             AND group_id = '{$csv_value}'";
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
        }
      }
    }
  }
} catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}

//
$dbh = null;

//性能結果を表示
memory_usage_end();
pro_end_time();

?>
