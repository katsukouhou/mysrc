<?php
/**
* Decryption
*
* 復号化の処理を管理
*
* @package none
* @subpackage none
*/
class Decryption {
  /**
  * __construct
  *
  * コンストラクタ関数
  *
  * @param none
  * @return none
  */
  public function __construct() {
  }

  /**
   * POST方式で復号値を取得する
   * @param string $url リクエストurl
   * @param array $data post用データ
   * @return string 復号されたデータ
   */
  function decode_object($data) {
    $postdata = http_build_query($data);
    $options = array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'content' => $postdata,
        'timeout' => 15 * 60 // 超时时间（单位:s）
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($this::DECODE_URL, false, $context);

    return json_decode($result,true)[$this::DATA_INDEX][$this::TEXT_INDEX];
  }

  /**
  * 復号URLを定義
  */
  const DECODE_URL = "http://nxbridge-api.inside.ai/decode.php";
  /**
  * dataインデックスを定義
  */
  const DATA_INDEX = 'data';
  /**
  * textインデックスを定義
  */
  const TEXT_INDEX = 'text';
}

?>
