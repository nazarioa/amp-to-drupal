<?php
require_once('settings.php');
require_once('Medoo/medoo.php');

$database = new Medoo($conSettings);

$results = $database->query("SELECT articles.id, articles.title, articles.test AS body, articles.publish, articles.datecreated, articles.shortdesc, articles.doc, articles.picture, articles.custom2 AS amp_ref_id, articles.custom3 AS language, GROUP_CONCAT(tags.name) AS tag_name FROM articles LEFT JOIN tags_items ON tags_items.item_id = articles.id LEFT JOIN tags ON tags.id = tags_items.tag_id WHERE tags_items.item_type = 'article' GROUP BY articles.id")->fetchAll();


$error = $database->error();
if($error[0] != '00000'){
  echo PHP_EOL;
  echo '__SQL Errors Found__' . PHP_EOL;
  var_dump($database->error());
  die();
  echo PHP_EOL;
}


$csv = '';
$csv .= 'id, title, body, publish, datecreated, shortdesc, doc, picture, amp_ref_id, language, tag_names' . "\n";

foreach ($results as $result) {

  if($result['datecreated'] == '0000-00-00 00:00:00'){
    $result['datecreated'] = '1999-01-01 00:00:01';
  }

  if( strlen($result['title']) > 125 ){
    $result['title'] = substr(trim($result['title']), 0, 125).'...';
  }

  $result['body'] = str_replace('"', '""', $result['body']);
  $result['body'] = str_replace('http://cesr.org', '/', $result['body']);
  $result['body'] = str_replace('https://cesr.org', '/', $result['body']);
  $result['body'] = str_replace('http://cesr.live.radicaldesigns.org', '/', $result['body']);
  $result['body'] = str_replace('https://cesr.live.radicaldesigns.org', '/', $result['body']);

  $result['shortdesc'] = str_replace('"', '""', $result['shortdesc']);
  $result['title'] = str_replace('"', '""', $result['title']);
  $result['picture'] = str_replace(' ', '_', $result['picture']);
  $result['doc'] = str_replace(' ', '_', $result['doc']);


  $csv .= '"' . $result['id'] . '", "' . utf8_encode($result['title']) . '", "' . utf8_encode($result['body']) . '", "' . $result['publish'] . '", "' . $result['datecreated'] . '", "' . utf8_encode($result['shortdesc']) . '", "' . utf8_encode($result['doc']) . '", "' . utf8_encode($result['picture']) . '", "' . utf8_encode($result['amp_ref_id']) . '", "' . utf8_encode($result['language']) . '", "' . utf8_encode($result['tag_name']) . '"' . "\n";
}

if(DEBUG == FALSE){
  $file = fopen($output_filename, 'w');
  fwrite($file, $csv);
  fclose($file);
  echo 'Done.' . PHP_EOL . 'Find the file at ' . __DIR__ . '/' . $output_filename;
}else{
  echo $csv;
}
