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
        //結果セットからレコードのデータをフェッチ
        while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
/*↓↓↓ [start]復号化を実施 <未実装> ↓↓↓*/
          $decrypted_result = $row['result'];
/*↑↑↑ [end]復号化を実施 <未実装> ↑↑↑*/
          //
          $db_recorder_array[$row['id']][$row['document_id']][$row['uri']][$row['entry_id']] = $decrypted_result;
        }

/*↓↓↓ [start]バリエーションを実施 ↓↓↓*/
        //
        foreach ($db_recorder_array as $id_key => $id_value) {
          foreach ($id_value as $document_id_key => $document_id_value) {
            foreach ($document_id_value as $uri_key => $uri_value) {
              foreach ($uri_value as $entry_id_key => $entry_id_value) {
                #code
                switch($entry_id_key){
                  case 'G'://生年月日 形式：YYYY/MM/DD
                      //
                      $validated_result = 'YYYY/MM/DD';
                      //UpdateSQL文を生成
/*↓↓↓ [start]code ↓↓↓*/
                      $update_sql .= "UPDATE transaction_cropped
                                      SET result = 'eeee'
                                      WHERE entry_id = 'G';";
/*↑↑↑ [end]code ↑↑↑*/
                      //ワーニングメッセージを設定
                      $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                          = 'converted to YYYY/MM/DD';
                      break;
                  case 'M'://郵便番号 形式：
                      //
                      $validated_result = 'xxx-xxxx';
                      //UpdateSQL文を生成
/*↓↓↓ [start]code ↓↓↓*/
                      $update_sql .= "UPDATE transaction_cropped
                                      SET result = 'ffff'
                                      WHERE entry_id = 'M';";
/*↑↑↑ [end]code ↑↑↑*/
                      //ワーニングメッセージを設定
                      $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                          = 'converted to xxx-xxxx';
                      break;
                  case 'Y'://口座_ゆうちょ 形式：
                      //
                      $validated_result = '????';
                      //UpdateSQL文を生成
/*↓↓↓ [start]code ↓↓↓*/
/*↑↑↑ [end]code ↑↑↑*/
                      //ワーニングメッセージを設定
                      $warnning_list[$document_id_key][$uri_key][$entry_id_key]
                          = 'converted to ????';
                      break;
                  default:
                      $validated_result = $decrypted_result;
                }
              }
            }
          }
        }
/*↑↑↑ [end]バリエーションを実施 ↑↑↑*/
       //DBデータを更新
       try{
         if ($update_sql) {
           //トランザクションを開始
           $dbh->beginTransaction();
           //DB更新を実施
           $dbh->exec($update_sql);
           //トランザクションをコミット
           $dbh->commit();
         }
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
