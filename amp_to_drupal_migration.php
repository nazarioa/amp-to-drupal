<?php
require_once('settings.php');
require_once('Medoo/medoo.php');

$database = new Medoo($conSettings);

$hardLinksToRoot = array('http://cesr.org', 'https://cesr.org', 'http://cesr.live.radicaldesigns.org', 'https://cesr.live.radicaldesigns.org');
$specialChractersToUnderscore = array(' ');
$whitespaceChractersToNull= array("\t", "\n", "\r", "\0", "\r\n", "\x0B", "\x80", '\x82', '\x83', "\x84", "\x93", "\x9d");

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

// First row is the header row
$csv .= 'id, title, body, publish, datecreated, shortdesc, doc, picture, amp_ref_id, language, tag_names' . "\n";

foreach ($results as $result) {

  if($result['datecreated'] == '0000-00-00 00:00:00'){
    $result['datecreated'] = '1999-01-01 00:00:01';
  }

  // Drupal has a hard char limit on the title field of 128
  // If it is longer than 125 lets truncate it and append ...
  if( strlen($result['title']) > 125 ){
    $result['title'] = substr(trim($result['title']), 0, 125).'...';
  }

  // Convert what is assumed to be ANSI to UTF-8.
  $result['title'] = mb_convert_encoding($result['title'], 'ANSII', 'UTF-8');
  $result['body'] = mb_convert_encoding($result['body'], 'ASCII', 'UTF-8');
  $result['shortdesc'] = mb_convert_encoding($result['shortdesc'], 'ASCII', 'UTF-8');
  $result['picture'] = mb_convert_encoding($result['picture'], 'ASCII', 'UTF-8');
  $result['doc'] = mb_convert_encoding($result['doc'], 'ASCII', 'UTF-8');

  // Search for line endings and replace with nothing
  $result['title'] = str_replace($whitespaceChractersToNull, '', $result['title']);
  $result['body'] = str_replace($whitespaceChractersToNull, '', $result['body']);
  $result['shortdesc'] = str_replace($whitespaceChractersToNull, '', $result['shortdesc']);
  $result['picture'] = str_replace($whitespaceChractersToNull, '', $result['picture']);
  $result['doc'] = str_replace($whitespaceChractersToNull, '', $result['doc']);

  // Replace hardlinks (things with http*://) to be /
  $result['body'] = str_replace($hardLinksToRoot, '/', $result['body']);

  // Replace 'SepcialCharactersToUnderscores' for doc and pictures field
  $result['picture'] = str_replace($specialChractersToUnderscore, '_', $result['picture']);
  $result['doc'] = str_replace($specialChractersToUnderscore, '_', $result['doc']);

  // Escaping Double-Quotes
  $result['shortdesc'] = str_replace('"', '""', $result['shortdesc']);
  $result['title'] = str_replace('"', '""', $result['title']);
  $result['body'] = str_replace('"', '""', $result['body']);

  $csv .= '"' . $result['id'] . '", "' . $result['title'] . '", "' . $result['body'] . '", "' . $result['publish'] . '", "' . $result['datecreated'] . '", "' . $result['shortdesc'] . '", "' . $result['doc'] . '", "' . $result['picture'] . '", "' . $result['amp_ref_id'] . '", "' . $result['language'] . '", "' . $result['tag_name'] . '"' . "\n";
}

if(DEBUG == FALSE){
  $file = fopen($output_filename, 'w');
  fwrite($file, $csv);
  fclose($file);
  echo PHP_EOL . PHP_EOL;
  echo '-------------------'.PHP_EOL;
  echo 'Done.' . PHP_EOL . 'Find the file at ' . __DIR__ . $output_filename;
}else{
  echo $csv;
}

?>
