<?php
require_once('settings.php');
require_once('Medoo/medoo.php');

// $query = 'SELECT id, title, test, published, datecreated, shortdesc, doc, picture, custom2, custom3 WHERE link IS NOT NULL AND doc IS NULL AND ';

$database = new Medoo($conSettings);
$results = $database->debug()->select(
  'articles',
  ['id', 'title', 'test(body)', 'publish', 'datecreated', 'shortdesc', 'doc', 'picture', 'custom2', 'custom3'],
  [ 'AND' => ['AND' => ['type[!]' => NULL, 'OR' => ['doc[!]' => NULL, 'body[!]' => NULL] ] ] ]
);

echo PHP_EOL;

if($database->error()[0] != '00000'){
  echo PHP_EOL;
  echo '__Errors Found__';
  echo PHP_EOL;
  var_dump($database->error());
  echo PHP_EOL;
}
