<?php
  /**
  * csv出力rootパスを定義
  */
  const CSV_ROOT_PATH = '/Applications/MAMP/htdocs/ie/home/paid_accounts/';
  /**
  * csv出力相対パスを定義
  */
  const RELATIVE_PATH = '/vpdm/csv/';

  /**
  * create_file_path
  *
  * ファイルパスを生成
  *
  * @param string $path
  * @param string $file_mode
  * @return none
  */
  function create_file_path($path, $file_mode) {
    if (!file_exists ($path)){
      mkdir ($path, $file_mode, true);
    }
  }

  /**
  * output_csv
  *
  * csvファイルを出力
  *
  * @param string file
  * @param bool $output_flag
  * @param bool $title_flag
  * @param string $title
  * @param array &$contents_array
  * @return none
  */
  function output_csv($file, $output_flag, $title_flag, $title, &$contents_array) {
    if ($title_flag) {
      $fp = fopen($file,'w');
      if ($output_flag) {
        //titleを出力
        fputcsv($fp, $title);
      }
    } else {
      $fp = fopen($file,'a');
    }
    //csvファイルへ出力
    foreach ($contents_array as $csv_key => $csv_value){
      foreach ($csv_value as $key => $value) {
        fputcsv($fp, $contents_array[$csv_key][$key]);
      }
    }
    //csvファイルをクローズ
    fclose($fp);
  }

?>
