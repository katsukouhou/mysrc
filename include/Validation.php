<?php
  //バリデーション処理
  function validate($document_id, $entry_id, $result)
  {
    $target_result = '';
    switch($entry_id){
      case 'A':
          //decryption
          //coding
          //
          $target_result = $result;
          break;
      case 'B':
          $target_result = $result;
          break;
      default:
          $target_result = $result;
    }

    return array($target_result, "111111");
  }
?>
