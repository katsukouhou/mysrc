<?php
  /**
  * CSVManager
  *
  * csvファイル出力を管理
  *
  * @package none
  * @subpackage none
  */
  class CSVManager {
    /**
    * __construct
    *
    * コンストラクタ関数
    *
    * @param none
    * @return none
    */
    public function __construct() {
      date_default_timezone_set('Asia/Tokyo');
      $this->csv_file_name = date("Y_m_d_His") . '.csv';
      $this->csv_title_output_flag = true;
      $this->csv_array = array();
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
      return $this::CSV_ROOT_PATH;
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
      return $this::RELATIVE_PATH;
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

    /**
    * getCSVFileFullPath
    *
    * csvフルパスを取得
    *
    * @param none
    * @return $this->csv_file_path . '/' . $this->csv_file_name
    */
    public function getCSVFileFullPath() {
      return $this->csv_file_path . $this->csv_file_name;
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
    public function create_file_path($organization_id, $document_id) {
      //
      $this->csv_file_path = $this->getCSVRootPath() . $organization_id .
                             $this->getCSVRelativePath() . $document_id . '/';
      //
      if (!file_exists ($this->csv_file_path)){
        mkdir ($this->csv_file_path, 0777, true);
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
    public function output_csv() {
      //
      if (!file_exists ($this->getCSVFileFullPath())) {
        $fp = fopen($this->getCSVFileFullPath(),'w');
        if ($this->csv_title_output_flag && $this->csv_title) {
          fputcsv($fp, $this->csv_title);
        }
      } else {
        $fp = fopen($this->getCSVFileFullPath(),'a');
      }

      //csvファイルへ出力
      foreach ($this->csv_array as $csv_key => $csv_value){
        foreach ($csv_value as $key => $value) {
          fputcsv($fp, $this->csv_array[$csv_key][$key]);
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
    private $csv_file_path;
    /**
    * csvファイル名
    */
    private $csv_file_name;
    /**
    * csvファイル表題
    */
    private $csv_title;
    /**
    * csv出力データ配列
    */
    public $csv_array;
  }

?>
