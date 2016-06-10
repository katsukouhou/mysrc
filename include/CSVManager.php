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

  class CSVManager {
    /**
    * __construct
    *
    * コンストラクタ関数
    *
    * @param
    * @param
    * @return none
    */
    public function __construct() {
      date_default_timezone_set('Asia/Tokyo');
      $this->csv_file_name = date("Y_m_d_His") . '.csv';
    }

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
    * getCSVRootPath
    *
    * csvファイルルートパスを取得
    *
    * @param none
    * @return CSV_ROOT_PATH
    */
    public function getCSVRootPath() {
      return CSV_ROOT_PATH;
    }

    /**
    * getCSVRelativePath
    *
    * csvファイル相対パスを取得
    *
    * @param none
    * @return RELATIVE_PATH
    */
    public function getCSVRelativePath() {
      return RELATIVE_PATH;
    }

    /**
    * __set
    *
    * 属性を設定
    *
    * @param $name
    * @param $value
    * @return none
    */
    public function __set($name, $value) {
      $this->$name = $value;
    }

    /**
    * __get
    *
    * 属性を取得
    *
    * @param $name
    * @return $this->$name
    */
    public function __get($name) {
      return $this->$name;
    }

    public function getCSVFileFullPath() {
      return $this->csv_file_full_path = $this->getCSVRootPath() . $this->organization_id .
             $this->getCSVRelativePath() . $this->document_id . '/' .
             $this->csv_file_name;
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
    public function output_csv() {
      if ($this->first_time_flag) {
        $fp = fopen($this->csv_file_full_path,'w');
        if ($this->csv_title_output_flag) {
          fputcsv($fp, $this->csv_title);
        }
      } else {
        $fp = fopen($this->csv_file_full_path,'a');
      }
      //csvファイルへ出力
      foreach ($this->contents_array as $csv_key => $csv_value){
        foreach ($csv_value as $key => $value) {
          fputcsv($fp, $this->contents_array[$csv_key][$key]);
        }
      }
      //csvファイルをクローズ
      fclose($fp);
    }

    /**
    * csv出力rootパス
    */
    const CSV_ROOT_PATH = '/Applications/MAMP/htdocs/ie/home/paid_accounts/';
    /**
    * csv出力相対パス
    */
    const RELATIVE_PATH = '/vpdm/csv/';
    /**
    * csv表題出力フラグ
    */
    private $csv_title_output_flag;
    /**
    * csvファイルフルパス
    */
    private $csv_file_full_path;
    /**
    * 初回出力フラグ
    */
    private $first_time_flag;
    /**
    * csvファイル表題
    */
    private $csv_title;
    /**
    * csv出力データ配列
    */
    private $contents_array;
    /**
    * organization_id
    */
    private $organization_id;
    /**
    * document_id
    */
    private $document_id;
    /**
    * csvファイル名
    */
    private $csv_file_name;
  }

?>
