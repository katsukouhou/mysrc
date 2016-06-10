<?php
/**
* 復号化を行うソースファイルの読み込み
*/
require_once './include/CSVManager.php';

$csv_manager = new CSVManager();
print $csv_manager->getCSVRootPath() . "\n";
print $csv_manager->getCSVRelativePath() . "\n";
?>
