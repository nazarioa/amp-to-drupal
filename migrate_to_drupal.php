<?php
define('MODE', '!debug');

require_once('settings.php');
require_once('Medoo/medoo.php');

$database = new Medoo($conSettings);

$results = $database->select(
  'articles',
  ['id', 'title', 'test(body)', 'publish', 'datecreated', 'shortdesc', 'doc', 'picture', 'custom2(amp_ref_id)', 'custom3(language)'],
  // ['link[=]' => NULL/* , 'LIMIT' =>20 */ ]
  [ 'OR' => ['doc[!]' => NULL, 'test[!]' => NULL]  , 'LIMIT' =>50 ]
);


// die();
echo PHP_EOL;

if($database->error()[0] != '00000'){
  echo PHP_EOL;
  echo '__Errors Found__';
  echo PHP_EOL;
  var_dump($database->error());
  die();
  echo PHP_EOL;
}


$csv = '';
$csv .= 'id, title, body, publish, datecreated, shortdesc, doc, picture, amp_ref_id, language' . "\n";

foreach ($results as $key => &$result) {

  if($result['datecreated'] == '0000-00-00 00:00:00'){
    $result['datecreated'] = '1999-01-01 00:00:01';
  }

  if( strlen($result['title']) > 125 ){
    $result['title'] = substr($result['title'], 0, 125).'...';
  }


  $csv .= '"' . $result['id'] . '", "' . addslashes(utf8_encode(trim($result['title']))) . '", "' . addslashes(utf8_encode($result['body'])) . '", "' . $result['publish'] . '", "' . $result['datecreated'] . '", "' . addslashes(utf8_encode($result['shortdesc'])) . '", "' . addslashes(utf8_encode($result['doc'])) . '", "' . addslashes(utf8_encode($result['picture'])) . '", "' . $result['amp_ref_id'] . '", "' . addslashes($result['language']) . '"' . "\n";

  echo PHP_EOL;
}

if(MODE != 'debug'){
  $file = fopen('./output.csv', 'w');
  fwrite($file, $csv);
  fclose($file);
}else{
  echo $csv;
}
