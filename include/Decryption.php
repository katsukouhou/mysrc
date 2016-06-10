<?php
//復号URLを指定
const DECODE_URL = "nxbridge-api.inside.ai/crypt/decode/";

/**
 * POST方式で復号値を取得する
 * @param string $url リクエストurl
 * @param array $data post用データ
 * @return string
 */
function decode_object($url, $data) {
  $postdata = http_build_query($post_data);
  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-type:application/x-www-form-urlencoded',
      'content' => $postdata,
      'timeout' => 15 * 60 // 超时时间（单位:s）
    )
  );
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);

  return $result;
}
?>
<?php
//POSTデータ
$data = array(
    'parts_d_code' => 'aaaaaa',
    'object' => 'xyzgjdlg'
);
$result = decode_object(DECODE_URL, $data);
//
echo '<送信データ>';
echo "<br />";
echo 'url:';
var_dump(DECODE_URL);
echo "<br />";
echo 'data:';
var_dump($data);
echo "<br />";
?>
<br /><受信データ>
<pre>
<?php
  if ($result) {
    var_dump($result);
  } else {
    print 'Request failed!' . "\n";
  }
?>
</pre>
