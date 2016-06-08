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

//
try{
  //
  mb_convert_variables('sjis-win','UTF-8',$csv_format);
  $array[$document_id]["format"] = $csv_format;

  //DBを接続
  $dbh = connect();
  //処理対象クエリ文字列を作成
  $sql_target = "SELECT id, document_id, entry_id, uri, result
                 FROM {$target_table}
                 WHERE status = {$unprocessed}";//******** document_idは？　必要！！
  //プリペアドステートメントを生成
  $stt = $dbh->prepare($sql_target);
  //プリペアドステートメントを実行
  $stt->execute();

  //結果セットからレコードのデータをフェッチ
  $query_num = $stt->rowCount();
  if($query_num != 0) {
    //クエリ処理を実行
    while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
      //処理対象リストにuriを追加
      if($csv_list[$row['document_id']]) {
        if(!in_array($row['uri'], $csv_list[$row['document_id']])) {
          $csv_list[$row['document_id']][] = $row['uri'];
        }
      } else {
        $csv_list[$row['document_id']][] = $row['uri'];
      }
      //resultを設定
      $array[$row['document_id']][$row['uri']][$row['entry_id']] =
        mb_convert_encoding($row['result'], 'sjis-win', 'UTF-8');//用調査！！！
    }

    //csvファイルパスを生成
    $csv_data_path = '/Applications/MAMP/htdocs/ie/csv/';
    if (!file_exists ($csv_data_path)){
      mkdir ("$csv_data_path", 0777, true);
    }
    //csvファイル名を生成
    $datetime = date("Y_m_d_His");//{YYYY_MM_DD_HHMMSS}.csv
    $csv_output_file = $csv_data_path . '/' . $datetime . '.csv';
    //csvファイルを生成
    $fp = fopen($csv_output_file,'w');
    //1行め目の項目を入力（表示の設定なら）
    if($visible_format){
      fputcsv($fp, $array[$document_id]["format"]);
    }
    //csvファイルへ出力
    foreach ($csv_list as $csv_key => $csv_value){
      foreach ($csv_value as $key => $value) {
        fputcsv($fp, $array[$csv_key][$value]);
      }
    }
    //csvファイルをクローズ
    fclose($fp);

    //csv生成が完了したら、DBのstatusを更新
    try {
      //トランザクションを開始
      $dbh->beginTransaction();
      foreach ($csv_list as $csv_key => $csv_value){
        foreach ($csv_value as $key => $value) {
          //処理対象クエリ文字列を作成
          $update_sql = "UPDATE {$target_table}
                         SET status = {$processed}
                         WHERE document_id = {$csv_key}
                         AND uri = '{$value}'";
          //DB更新を実施
          $dbh->exec($update_sql);
        }
      }
      //トランザクションをコミット
      $dbh->commit();

/*↓↓↓ [start]NXへ通知 <未実装> ➡︎最新方針より必要がなくなる？↓↓↓*/
      #code
/*↑↑↑ [end]NXへ通知 <未実装> ↑↑↑*/

    } catch (Exception $e) {
      //トランザクションをロールバック
      $dbh->rollBack();
      print('DB update Failed:' . $e->getMessage());
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
