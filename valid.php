<?php
/*****-- DB接続を行うソースファイルの読み込み --*****/
require_once './include/DBManager.php';
require_once './include/Validation.php';

/*****-- カスタマイズ項目 --*****/
date_default_timezone_set('Asia/Tokyo');
set_time_limit(120);
$start = microtime( TRUE );

//txtファイルパスを生成
$output_file_path = '/Applications/MAMP/htdocs/ie/txt/';
if (!file_exists ($output_file_path)){
  mkdir ("$output_file_path", 0777, true);
}
//csvファイル名を生成
$datetime = date("Y_m_d_His");//{YYYY_MM_DD_HHMMSS}.txt
$output_file = $output_file_path . '/' . $datetime . '.txt';

//
try{
  //DBを接続
  $dbh = connect();
  //処理対象クエリ文字列を作成
  $sql_target = "SELECT id, document_id, entry_id, uri, result
                 FROM {$target_table}
                 WHERE status = {$unprocessed}";
  //プリペアドステートメントを生成
  $stt = $dbh->prepare($sql_target);
  //プリペアドステートメントを実行
  $stt->execute();

  ////結果セットからレコードのデータをフェッチ
  $query_num = $stt->rowCount();
  if($query_num != 0) {
    //
    try {
      //トランザクションを開始
      $dbh->beginTransaction();
      //結果セットからレコードのデータをフェッチ
      while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
        //復号を実施 <未実装>
        $decrypted_result = $row['result'];
        //バリデーションを実施
        list($validated_result, $additional_entry_id, $adddtional_value, $message)
          = validate($row['document_id'], $row['entry_id'], $decrypted_result);

        //DB更新文を生成
        $update_sql = "UPDATE {$target_table}
                       SET result = '{$validated_result}'
                       WHERE document_id = {$row['document_id']}
                       AND entry_id = '{$row['entry_id']}'
                       AND uri = '{$row['uri']}'";
        //DB更新を実施
        $dbh->exec($update_sql);

        //マスタファイルにより対象フィールドの値を更新
        if ($additional_entry_id) {
          //DB更新文を生成
          $update_sql = "UPDATE {$target_table}
                         SET result = '{$adddtional_value}'
                         WHERE document_id = {$row['document_id']}
                         AND entry_id = '{$additional_entry_id}'
                         AND uri = '{$row['uri']}'";
          //DB更新を実施
          $dbh->exec($update_sql);
        }

        //ワーニングメッセージを設定
        $warnning_list[$row['document_id']][$row['uri']][$row['entry_id']] = $message;
      }
      //トランザクションをコミット
      $dbh->commit();

    } catch (Exception $e) {
      //トランザクションをロールバック
      $dbh->rollBack();
      print('DB update Failed: ' . $e->getMessage());
    }

    //txtファイルを生成
    $fp = fopen($output_file,'w');
    //バリデーションのワーニングを出力
    $header_format = '[document_id] [uri] [entry_id] [message]' . "\n";
    fwrite($fp, $header_format);
    foreach ($warnning_list as $document_key => $document_value){
      foreach ($warnning_list[$document_key] as $uri_key => $uri_value){
        foreach ($warnning_list[$document_key][$uri_key] as $entry_key => $entry_value) {
          $message = $document_key . ' ' . $uri_key . ' ' . $entry_key . ' ' . $entry_value . "\n";
          fwrite($fp, $message);
        }
      }
    }
    //ファイルをクローズ
    fclose($fp);
  }

} catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}

//
$dbh = null;

?>
