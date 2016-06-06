<?php
  //バリデーション処理
  function validate()
  {
    /*****-- DB接続を行うソースファイルの読み込み --*****/
    require_once 'DBManager.php';

    //
    try {
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

      //結果セットからレコードのデータをフェッチ
      $query_num = $stt->rowCount();
      if($query_num != 0) {
        //
        try {
          //トランザクションを開始
          $dbh->beginTransaction();
          //結果セットからレコードのデータをフェッチ
          while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
/*↓↓↓ [start]復号化を実施 <未実装> ↓↓↓*/
            $decrypted_result = $row['result'];
/*↑↑↑ [end]復号化を実施 <未実装> ↑↑↑*/

/*↓↓↓ [start]バリエーションを実施 ↓↓↓*/
            switch($row['entry_id']){
              case 'G'://生年月日 形式：YYYY/MM/DD
                  #code
                  $validated_result = 'YYYY/MM/DD';
                  break;
              case 'M'://郵便番号 形式：
                  $validated_result = 'XXX-XXXX';
                  break;
              default:
                  $validated_result = $decrypted_result;
            }

            //DB更新文を生成
            $update_sql = "UPDATE {$target_table}
                           SET result = '{$validated_result}'
                           WHERE document_id = {$row['document_id']}
                           AND entry_id = '{$row['entry_id']}'
                           AND uri = '{$row['uri']}'";
            //DB更新を実施
            $dbh->exec($update_sql);

            /*
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
            */
/*↑↑↑ [end]バリエーションを実施 ↑↑↑*/

            //ワーニングメッセージを設定
            $warnning_list[$row['document_id']][$row['uri']][$row['entry_id']]
              = 'customized-message';
          }
          //トランザクションをコミット
          $dbh->commit();

        } catch (Exception $e) {
          //トランザクションをロールバック
          $dbh->rollBack();
          print('DB update Failed:' . $e->getMessage());
        }
      }
    } catch (PDOException $e) {
      print('Error:'.$e->getMessage());
      die();
    }
    //
    return $warnning_list;
  }
?>
