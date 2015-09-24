<?php
require_once('settings.php');
require_once('Medoo/medoo.php');

$database = new Medoo($conSettings);

$results = $database->select(
  'articles',
  array('id', 'title', 'test(body)', 'publish', 'datecreated', 'shortdesc', 'doc', 'picture', 'custom2(amp_ref_id)', 'custom3(language)'),
  // ['link[=]' => NULL/* , 'LIMIT' =>20 */ ]
  array( 'OR' => array('doc[!]' => NULL, 'test[!]' => NULL), 'LIMIT' => 300 )
);


// die();
echo PHP_EOL;

$error = $database->error();
if($error[0] != '00000'){
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
    $result['title'] = substr(trim($result['title']), 0, 125).'...';
  }

  $result['body'] = str_replace('"', '""', $result['body']);
  $result['body'] = str_replace('http://cesr.org', '/', $result['body']);

  $result['shortdesc'] = str_replace('"', '""', $result['shortdesc']);
  $result['title'] = str_replace('"', '""', $result['title']);
  $result['picture'] = str_replace(' ', '_', $result['picture']);
  $result['doc'] = str_replace(' ', '_', $result['doc']);


  $csv .= '"' . $result['id'] . '", "' . utf8_encode($result['title']) . '", "' . utf8_encode($result['body']) . '", "' . $result['publish'] . '", "' . $result['datecreated'] . '", "' . utf8_encode($result['shortdesc']) . '", "' . utf8_encode($result['doc']) . '", "' . utf8_encode($result['picture']) . '", "' . utf8_encode($result['amp_ref_id']) . '", "' . utf8_encode($result['language']) . '"' . "\n";

  echo PHP_EOL;
}

if(DEBUG == FALSE){
  $file = fopen($output_filename, 'w');
  fwrite($file, $csv);
  fclose($file);
}else{
  echo $csv;
}