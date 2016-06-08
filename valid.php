<?php
/*****-- 性能テスト用のソースファイルの読み込み --*****/
require_once './include/Utility.php';
memory_usage_start();
pro_start_time();

/*****-- DB接続を行うソースファイルの読み込み --*****/
require_once './include/Validation.php';

/*****-- カスタマイズ項目 --*****/
date_default_timezone_set('Asia/Tokyo');
set_time_limit(0);

//
try{
  //バリデーションを実施
  $warnning_list = validate();

  //ワーニングを出力
  if ($warnning_list) {
    //txtファイルパスを生成
    $output_file_path = '/Applications/MAMP/htdocs/ie/txt/';
    if (!file_exists ($output_file_path)){
      mkdir ("$output_file_path", 0777, true);
    }
    //txtファイル名を生成
    $datetime = date("Y_m_d_His");//{YYYY_MM_DD_HHMMSS}.txt
    $output_file = $output_file_path . '/' . $datetime . '.txt';
    //txtファイルを生成
    $fp = fopen($output_file,'w');

    //バリデーションのワーニングを出力
    $header_format = '[document_id] [uri] [entry_id] [message]' . "\n";
    fwrite($fp, $header_format);
    foreach ($warnning_list as $document_key => $document_value){
      foreach ($warnning_list[$document_key] as $uri_key => $uri_value){
        foreach ($warnning_list[$document_key][$uri_key] as $entry_key => $entry_value) {
          //
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

//性能結果を表示
memory_usage_end();
pro_end_time();

//
$a = array(
  'A' => 'aaaa',
  'B' => 'bbbb',
  'C' => 'cccc',
  'D' => 'dddd'
);

$b = array(
  'C' => 'cccc',
  'A' => 'aaaa',
  'D' => 'dddd',
  'B' => 'bbbb'
);

var_dump($a);
var_dump($b);

?>
