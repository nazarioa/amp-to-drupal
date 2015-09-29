<?php
require_once('settings.php');
require_once('Medoo/medoo.php');

$database = new Medoo($conSettings);

$hardLinksToRoot = array('http://cesr.org', 'https://cesr.org', 'http://cesr.live.radicaldesigns.org', 'https://cesr.live.radicaldesigns.org');
$specialChractersToUnderscore = array(' ');
$whitespaceChractersToNull= array("\t", "\n", "\r", "\0", "\r\n", "\x0B", "\x80", '\x82', '\x83', "\x84", "\x93", "\x9d");

$results = $database->query("SELECT articles.id, articles.title, articles.test AS body, articles.publish, articles.datecreated, articles.shortdesc, articles.doc, articles.picture, articles.custom2 AS amp_ref_id, articles.custom3 AS language, GROUP_CONCAT(tags.name) AS tag_name FROM articles LEFT JOIN tags_items ON tags_items.item_id = articles.id LEFT JOIN tags ON tags.id = tags_items.tag_id WHERE tags_items.item_type = 'article' GROUP BY articles.id LIMIT 40")->fetchAll();


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


  // Fix weird characters -- margo's code
  $result['title'] = fixMSWord($result['title']);
  $result['body'] = fixMSWord($result['body']);
  $result['shortdesc'] = fixMSWord($result['shortdesc']);
  $result['picture'] = fixMSWord($result['picture']);
  $result['doc'] = fixMSWord($result['doc']);

  // Search for line endings and replace with nothing
  $result['title'] = str_replace($whitespaceChractersToNull, '', $result['title']);
  $result['body'] = str_replace($whitespaceChractersToNull, '', $result['body']);
  $result['shortdesc'] = str_replace($whitespaceChractersToNull, '', $result['shortdesc']);
  $result['picture'] = str_replace($whitespaceChractersToNull, '', $result['picture']);
  $result['doc'] = str_replace($whitespaceChractersToNull, '', $result['doc']);

  // Replace hardlinks (things with http*://) to be /
  $result['body'] = str_replace($hardLinksToRoot, '/', $result['body']);

  // Escaping Double-Quotes
  $result['shortdesc'] = str_replace('"', '""', $result['shortdesc']);
  $result['title'] = str_replace('"', '""', $result['title']);
  $result['body'] = str_replace('"', '""', $result['body']);

  // Replace 'SepcialCharactersToUnderscores' for doc and pictures field
  $result['picture'] = str_replace($specialChractersToUnderscore, '_', $result['picture']);
  $result['doc'] = str_replace($specialChractersToUnderscore, '_', $result['doc']);


  $csv .= '"' . $result['id'] . '", "' . utf8_encode($result['title']) . '", "' . utf8_encode($result['body']) . '", "' . $result['publish'] . '", "' . $result['datecreated'] . '", "' . utf8_encode($result['shortdesc']) . '", "' . utf8_encode($result['doc']) . '", "' . utf8_encode($result['picture']) . '", "' . utf8_encode($result['amp_ref_id']) . '", "' . utf8_encode($result['language']) . '", "' . utf8_encode($result['tag_name']) . '"' . "\n";
}

if(DEBUG == FALSE){
  $file = fopen($output_filename, 'w');
  fwrite($file, $csv);
  fclose($file);
  echo PHP_EOL . PHP_EOL;
  echo '-------------------'.PHP_EOL;
  echo 'Done.' . PHP_EOL . 'Find the file at ' . __DIR__ . '/' . $output_filename;
}else{
  echo $csv;
}


function fixMSWord($string) {
    $map = Array(
        '33' => '!', '34' => '"', '35' => '#', '36' => '$', '37' => '%', '38' => '&', '39' => "'", '40' => '(', '41' => ')', '42' => '*',
        '43' => '+', '44' => ',', '45' => '-', '46' => '.', '47' => '/', '48' => '0', '49' => '1', '50' => '2', '51' => '3', '52' => '4',
        '53' => '5', '54' => '6', '55' => '7', '56' => '8', '57' => '9', '58' => ':', '59' => ';', '60' => '<', '61' => '=', '62' => '>',
        '63' => '?', '64' => '@', '65' => 'A', '66' => 'B', '67' => 'C', '68' => 'D', '69' => 'E', '70' => 'F', '71' => 'G', '72' => 'H',
        '73' => 'I', '74' => 'J', '75' => 'K', '76' => 'L', '77' => 'M', '78' => 'N', '79' => 'O', '80' => 'P', '81' => 'Q', '82' => 'R',
        '83' => 'S', '84' => 'T', '85' => 'U', '86' => 'V', '87' => 'W', '88' => 'X', '89' => 'Y', '90' => 'Z', '91' => '[', '92' => '\\',
        '93' => ']', '94' => '^', '95' => '_', '96' => '`', '97' => 'a', '98' => 'b', '99' => 'c', '100'=> 'd', '101'=> 'e', '102'=> 'f',
        '103'=> 'g', '104'=> 'h', '105'=> 'i', '106'=> 'j', '107'=> 'k', '108'=> 'l', '109'=> 'm', '110'=> 'n', '111'=> 'o', '112'=> 'p',
        '113'=> 'q', '114'=> 'r', '115'=> 's', '116'=> 't', '117'=> 'u', '118'=> 'v', '119'=> 'w', '120'=> 'x', '121'=> 'y', '122'=> 'z',
        '123'=> '{', '124'=> '|', '125'=> '}', '126'=> '~', '127'=> ' ', '128'=> '&#8364;', '129'=> ' ', '130'=> ',', '131'=> ' ', '132'=> '"',
        '133'=> '.', '134'=> ' ', '135'=> ' ', '136'=> '^', '137'=> ' ', '138'=> ' ', '139'=> '<', '140'=> ' ', '141'=> ' ', '142'=> ' ',
        '143'=> ' ', '144'=> ' ', '145'=> "'", '146'=> "'", '147'=> '"', '148'=> '"', '149'=> '.', '150'=> '-', '151'=> '-', '152'=> '~',
        '153'=> ' ', '154'=> ' ', '155'=> '>', '156'=> ' ', '157'=> ' ', '158'=> ' ', '159'=> ' ', '160'=> ' ', '161'=> '¡', '162'=> '¢',
        '163'=> '£', '164'=> '¤', '165'=> '¥', '166'=> '¦', '167'=> '§', '168'=> '¨', '169'=> '©', '170'=> 'ª', '171'=> '«', '172'=> '¬',
        '173'=> '­', '174'=> '®', '175'=> '¯', '176'=> '°', '177'=> '±', '178'=> '²', '179'=> '³', '180'=> '´', '181'=> 'µ', '182'=> '¶',
        '183'=> '·', '184'=> '¸', '185'=> '¹', '186'=> 'º', '187'=> '»', '188'=> '¼', '189'=> '½', '190'=> '¾', '191'=> '¿', '192'=> 'À',
        '193'=> 'Á', '194'=> 'Â', '195'=> 'Ã', '196'=> 'Ä', '197'=> 'Å', '198'=> 'Æ', '199'=> 'Ç', '200'=> 'È', '201'=> 'É', '202'=> 'Ê',
        '203'=> 'Ë', '204'=> 'Ì', '205'=> 'Í', '206'=> 'Î', '207'=> 'Ï', '208'=> 'Ð', '209'=> 'Ñ', '210'=> 'Ò', '211'=> 'Ó', '212'=> 'Ô',
        '213'=> 'Õ', '214'=> 'Ö', '215'=> '×', '216'=> 'Ø', '217'=> 'Ù', '218'=> 'Ú', '219'=> 'Û', '220'=> 'Ü', '221'=> 'Ý', '222'=> 'Þ',
        '223'=> 'ß', '224'=> 'à', '225'=> 'á', '226'=> 'â', '227'=> 'ã', '228'=> 'ä', '229'=> 'å', '230'=> 'æ', '231'=> 'ç', '232'=> 'è',
        '233'=> 'é', '234'=> 'ê', '235'=> 'ë', '236'=> 'ì', '237'=> 'í', '238'=> 'î', '239'=> 'ï', '240'=> 'ð', '241'=> 'ñ', '242'=> 'ò',
        '243'=> 'ó', '244'=> 'ô', '245'=> 'õ', '246'=> 'ö', '247'=> '÷', '248'=> 'ø', '249'=> 'ù', '250'=> 'ú', '251'=> 'û', '252'=> 'ü',
        '253'=> 'ý', '254'=> 'þ', '255'=> 'ÿ'
    );
     $search = Array();
    $replace = Array();

    foreach ($map as $s => $r) {
        $search[] = chr((int)$s);
        $replace[] = $r;
    }

    return str_replace($search, $replace, $string);
}
?>
