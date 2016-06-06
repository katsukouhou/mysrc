<?php
  //csv処理フラグ
  $unprocessed = 1;
  $processed = 2;


  //対象DBテーブル
  $target_table = 'transaction_cropped';

  //
  function connect() {
    //DBアクセスのパラメタを設定
    $dsn = 'mysql:host=127.0.0.1;dbname=test';
    $username = 'root';
    $password = 'root';
    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );

    try {
      //DBを接続
      $dbh = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
      exit("DB connecting error: {$e->getMessage()}");
    }
    return $dbh;
  }
?>
