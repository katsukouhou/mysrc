<?php
/**
* 復号化を行うソースファイルの読み込み
*/
require_once './include/CSVManager.php';

$csv_manager = new CSVManager();
print $csv_manager->getCSVRootPath() . "\n";
print $csv_manager->getCSVRelativePath() . "\n";

$csv_manager->organization_id = '111';
$csv_manager->document_id = "20001";
print $csv_manager->getCSVFileFullPath() . "\n";
print $csv_manager->csv_file_full_path . "\n";
?>
