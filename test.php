<?php
/**
* 復号化を行うソースファイルの読み込み
*/
require_once './include/CSVManager.php';

/**
* @global array $GLOBALS['_csv_data_output_list']
* @name $_csv_data_output_list
*/
$GLOBALS['_csv_data_output_list'] = array();

$csv_manager = new CSVManager();
print $csv_manager->getCSVRootPath() . "\n";
print $csv_manager->getCSVRelativePath() . "\n";
?>
