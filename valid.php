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
$warnning_list = null;

//warnning format
$file_format = array(
  '[document_id]',
  '[uri]',
  '[entry_id]',
  '[message]'
);

/*
//バリデーション処理
function validate($document_id, $uri, $entry_id, $result)
{
  global $warnning_list;

  $target_result = '';
  switch($entry_id){
    case 'A':
        //decryption
        //coding
        //
        $target_result = $result;
        //
        $warnning_list[$document_id][$uri][$entry_id] = 'A-converted!';
        break;
    case 'B':
        $target_result = $result;
        break;
    default:
        $target_result = $result;
  }

  return $target_result;
}
*/

//
mb_convert_variables('SJIS','UTF-8',$file_format);
$array[$document_id]["format"] = $file_format;

//
try{
  //DBを接続
  $dbh = connect();
  //処理対象クエリ文字列を作成
  $sql_target = 'SELECT id, document_id, entry_id, uri, result
                 FROM transaction_cropped
                 WHERE status = ' . UNPROCESSED;
  //プリペアドステートメントを生成
  $stt = $dbh->prepare($sql_target);
  //プリペアドステートメントを実行
  $stt->execute();

  //
  try {
    //トランザクションを開始
    $dbh->beginTransaction();
    //結果セットからレコードのデータをフェッチ
    while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
      //復号を実施
      $decrypted_result = $row['result'];//--復号処理に変更必要--
      //バリデーションを実施
      list($validated_result, $message) = validate($row['document_id'],
                                                   $row['entry_id'],
                                                   $decrypted_result);
      //
      //print 'result: ' . $validated_result . ' message: ' . $message . "\n";


      //DB更新分を生成
      $update_sql = "UPDATE transaction_cropped
                     SET result = '{$validated_result}'
                     WHERE document_id = {$row['document_id']}
                     AND entry_id = '{$row['entry_id']}'
                     AND uri = '{$row['uri']}'";

      print $update_sql . "\n";
      //マスタファイルにより対象フィールドの値を更新
      //coding

      //DB更新を実施
      $dbh->exec($update_sql);
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
  fputcsv($fp, $array[$document_id]["format"]);
  if( $output_file ){
    fwrite($fp, var_export($warnning_list, TRUE));
    //
    /*
    foreach ($warnning_list as $document_key => $document_value){
      foreach ($warnning_list[$document_key] as $uri_key => $uri_value){
        foreach ($warnning_list[$document_key][$uri_key] as $entry_key => $entry_value) {
          $message = $document_key . ' ' . $uri_key . ' ' . $entry_key . ' ' . $entry_value . "\n";
          //fwrite($fp, $message);
        }
      }
    }
    */
  }

  //ファイルをクローズ
  fclose($fp);
} catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}

//
$dbh = null;

?>
