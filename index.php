
<meta charset="UTF-8">
<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
if(empty($_GET['isbn'])){
   echo 'Введите isbn<br>'
    . '<form method="get" action=""><input type="text" name="isbn"><br>'
           . 'Введите тип файла<br>'
           . '<input type="text" name="type"><br>'
           . 'Введите размер<br>'
           . '<input type="text" name="mb"><br>'
           . '<input type="submit" value="Search"></form>'; 
   die;
}


$info = getBookInfo($_GET['isbn'],'AKIAI35ZR23V6JAS62HQ','bpOh1IfpeyJuBr0XXisuqe4CUCVcxcc0bellU3Hi');

$xml = simplexml_load_string($info);
//print_r($xml->Items->Item);
$forEcho['isbn'] = $xml->Items->Item->ItemAttributes->ISBN;
$forEcho['pages'] = $xml->Items->Item->ItemAttributes->NumberOfPages;
$forEcho['date'] = $xml->Items->Item->ItemAttributes->ReleaseDate;
if(empty($forEcho['date'])){
    $forEcho['date'] = $xml->Items->Item->ItemAttributes->PublicationDate;
}
$forEcho['date'][0]= substr($forEcho['date'],0,4);
$forEcho['pic'] = $xml->Items->Item->LargeImage->URL;
$forEcho['title'] = $xml->Items->Item->ItemAttributes->Title;
$forEcho['author'] = $xml->Items->Item->ItemAttributes->Author;
if(empty($forEcho['author'])){
    $forEcho['author'] = $xml->Items->Item->ItemAttributes->Creator;
}
if(count($forEcho['author'])> 1){
    for($i=0;$i<count($forEcho['author']);$i++){
        if($i == 0){
            $forEcho['author'][0] = $forEcho['author'][$i];
        }else{
            $forEcho['author'][0] .= ', '.$forEcho['author'][$i];
        }
        
    }
}
$forEcho['content'] = $xml->Items->Item->EditorialReviews->EditorialReview->Content;
foreach ($forEcho as $key => $value) {
    if(empty($value)){
        //echo '<FONT color="red">Not found:'.$key.' </FONT><br> ';
    }
    
}
//print_r($forEcho);

echo '<a href="'.$forEcho['pic'][0].'">'.$forEcho['pic'][0].'</a><br><plainttext>[center][b]'.$forEcho['title'][0].' by '.$forEcho['author'][0].'[/b]<br>'.$forEcho['date'][0].' | ISBN: '.$forEcho['isbn'][0].''
        . ' | English | '.$forEcho['pages'].' pages | '.$_GET['type'].' | '.$_GET['mb'].' MB  [/center]</plaintext><br>'.$forEcho['content'];

//echo $info; 
 
function getBookInfo($isbn, $access_key, $secure_access_key)
{
  // формируем список параметров запроса
  $fields = array();
  $fields['AWSAccessKeyId'] = $access_key;
  $fields['ItemId'] = $isbn;
  $fields['MerchantId'] = 'All';
  $fields['Operation'] = 'ItemLookup';
  $fields['ResponseGroup'] = 'Request,Large';
  $fields['Service'] = 'AWSECommerceService';
  $fields['Version'] = '2014-10-19';
  $fields['AssociateTag'] = 'aaaaabbbbbcccc';
  $fields['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
 
  // сортируем параметры согласно спецификации Amazon API
  ksort($fields);
 
  $query = array();
  foreach ($fields as $key=>$value) {
    $query[] = "$key=" . urlencode($value);
  }
 
  // подписываем запрос секретным ключом
  $string = "GET\nwebservices.amazon.com\n/onca/xml\n" . implode('&', $query);
  $signed = urlencode(base64_encode(hash_hmac('sha256', $string, $secure_access_key, true)));
 
  // формируем строку запроса к сервису
  $url = 'http://webservices.amazon.com/onca/xml?' . implode('&', $query) . '&Signature=' . $signed;
 
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $data = curl_exec($ch);
  $info = curl_getinfo($ch);
 
  if ($info['http_code'] != '200') return false;
 
  return $data;
}
?>