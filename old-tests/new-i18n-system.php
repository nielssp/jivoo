<?php
use Jivoo\Core\I18n\Locale;
use Jivoo\Core\I18n\LazyLocale;
use Jivoo\Core\Json;
require '../src/bootstrap.php';

function readMo2($file) {
  $f = file_get_contents($file);
  
  if (!$f) {
    trigger_error('Could not open file: ' . $file, E_USER_ERROR);
    return null;
  }
  
  $magic = bin2hex(substr($f, 0, 4));
  if ($magic === '950412de') { // Big endian
    $header = 'Nrev/NN/NO/NT/NS/NH';
    $row = 'Nlength/Noffset';
  }
  else if ($magic === 'de120495') { // Little endian
    $header = 'Vrev/VN/VO/VT/VS/VH';
    $row = 'Vlength/Voffset';
  }
  else {
    trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
    return null;
  }
  $o = 4;
  
  $data = unpack($header, substr($f, $o, 24));
  $num = $data['N'];
  $oOffset = $data['O'];
  $tOffset = $data['T'];
  
  $o = $oOffset;
  $offsets = array();
  for ($i = 0; $i < $num; $i++) {
    $offsets[$i] = array('message' => unpack($row, substr($f, $o, 8)));
    $o += 8;
  }
  $o = $tOffset;
  for ($i = 0; $i < $num; $i++) {
    $offsets[$i]['translation'] = unpack($row, substr($f, $o, 8));;
    $o += 8;
  }
  
  $messages = array();
  foreach ($offsets as $i => $offset) {
    $o = $offset['message']['offset'];
    $message = '';
    if ($offset['message']['length'] > 0)
      $message = substr($f, $o, $offset['message']['length']);
    $hasNul = strpos($message, "\0");
    if ($hasNul !== false)
      $message = substr($message, $hasNul + 1); // gets plural
    $messages[$i] = $message;
  }
  $l = new Locale();
  foreach ($offsets as $i => $offset) {
    $o = $offset['translation']['offset'];
    if ($offset['translation']['length'] > 0) {
      $translation = substr($f, $o, $offset['translation']['length']);
      if ($messages[$i] == '') {
        $properties = explode("\n", $translation);
        foreach ($properties as $property) {
          list($property, $value) = explode(':', $property, 2);
          if (trim(strtolower($property)) == 'plural-forms') {
            $l->pluralForms = $value;
            break;
          }
        }
      }
      if (strpos($translation, "\0") !== false)
        $translation = explode("\0", $translation);
      $l->set($messages[$i], $translation);
    }
  }
  
//   foreach (self::$properties as $property) {
//     if ($l->hasProperty($property))
//       $l->$property = $l->getProperty($property);
//   }
  return $l;
}

function readMo3($file) {
  $f = file_get_contents($file);

  if (!$f) {
    trigger_error('Could not open file: ' . $file, E_USER_ERROR);
    return null;
  }

  $magic = bin2hex(substr($f, 0, 4));
  if ($magic === '950412de') { // Big endian
    $header = 'Nrev/NN/NO/NT/NS/NH';
    $format = 'N';
  }
  else if ($magic === 'de120495') { // Little endian
    $header = 'Vrev/VN/VO/VT/VS/VH';
    $format = 'V';
  }
  else {
    trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
    return null;
  }
  $o = 4;

  $data = unpack($header, substr($f, $o, 24));
  $num = $data['N'];
  $oOffset = $data['O'];
  $tOffset = $data['T'];
  
  if ($num == 0)
    return new Locale();
  
  $format = $format . ($num * 2);

  $o = $oOffset;
  $oTable = unpack($format, substr($f, $o, 8 * $num));
  $o = $tOffset;
  $tTable = unpack($format, substr($f, $o, 8 * $num));
  
  $offsets = array();
  $n = $num * 2;
  $o = $oTable[2];
  $messages = array();
  for ($i = 1; $i <= $n; $i += 2) {
    $length = $oTable[$i];
    if ($length == 0) {
      $message = '';
      $o += 1;
    }
    else {
      $message = substr($f, $o, $length);
      $o += $length + 1;
      $hasNul = strpos($message, "\0");
      if ($hasNul !== false)
        $message = substr($message, $hasNul + 1); // gets plural
    }
    $messages[$i] = $message;
  }
  $o = $tTable[2];
  $l = new Locale();
  for ($i = 1; $i <= $n; $i += 2) {
    $length = $tTable[$i];
    if ($length > 0) {
      $translation = substr($f, $o, $length);
      $o += $length + 1;
      if ($messages[$i] == '') {
        $properties = explode("\n", $translation);
        foreach ($properties as $property) {
          list($property, $value) = explode(':', $property, 2);
          if (trim(strtolower($property)) == 'plural-forms') {
            $l->pluralForms = $value;
            break;
          }
        }
      }
      if (strpos($translation, "\0") !== false)
        $translation = explode("\0", $translation);
      $l->set($messages[$i], $translation);
    }
    else {
      $o += 1;
    }
  }
  return $l;
}

function readMo4($file) {
  $f = fopen($file, 'r');

  if (!$f) {
    trigger_error('Could not open file: ' . $file, E_USER_ERROR);
    return null;
  }

  $magic = bin2hex(fread($f, 4));
  if ($magic === '950412de') { // Big endian
    $header = 'Nrev/NN/NO/NT/NS/NH';
    $format = 'N';
  }
  else if ($magic === 'de120495') { // Little endian
    $header = 'Vrev/VN/VO/VT/VS/VH';
    $format = 'V';
  }
  else {
    trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
    return null;
  }

  $data = unpack($header, fread($f, 6 * 4));
  $num = $data['N'];
  $oOffset = $data['O'];
  $tOffset = $data['T'];

  if ($num == 0)
    return new Locale();

  $format = $format . ($num * 2);

  fseek($f, $oOffset);
  $oTable = unpack($format, fread($f, 8 * $num));
  $tTable = unpack($format, fread($f, 8 * $num));

  $offsets = array();
  $n = $num * 2;
  fseek($f, $oTable[2]);
  $messages = array();
  for ($i = 1; $i <= $n; $i += 2) {
    $length = $oTable[$i];
    if ($length == 0) {
      $message = '';
      fread($f, 1);
    }
    else {
      $message = substr(fread($f, $length + 1), 0, -1);
      $hasNul = strpos($message, "\0");
      if ($hasNul !== false)
        $message = substr($message, $hasNul + 1); // gets plural
    }
    $messages[$i] = $message;
  }
  fseek($f, $tTable[2]);
  $l = new Locale();
  for ($i = 1; $i <= $n; $i += 2) {
    $length = $tTable[$i];
    if ($length > 0) {
      $translation = substr(fread($f, $length + 1), 0, -1);
      if ($messages[$i] == '') {
        $properties = explode("\n", $translation);
        foreach ($properties as $property) {
          list($property, $value) = explode(':', $property, 2);
          if (trim(strtolower($property)) == 'plural-forms') {
            $l->pluralForms = $value;
            break;
          }
        }
      }
      if (strpos($translation, "\0") !== false)
        $translation = explode("\0", $translation);
      $l->set($messages[$i], $translation);
    }
    else {
      fread($f, 1);
    }
  }
  fclose($f);
  return $l;
}

function readMo5($file) {
  $f = file_get_contents($file);

  if (!$f) {
    trigger_error('Could not open file: ' . $file, E_USER_ERROR);
    return null;
  }

  $magic = bin2hex(substr($f, 0, 4));
  if ($magic === '950412de') { // Big endian
    $header = 'Nrev/NN/NO/NT/NS/NH';
    $format = 'N';
  }
  else if ($magic === 'de120495') { // Little endian
    $header = 'Vrev/VN/VO/VT/VS/VH';
    $format = 'V';
  }
  else {
    trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
    return null;
  }
  $o = 4;

  $data = unpack($header, substr($f, $o, 24));
  $num = $data['N'];
  $oOffset = $data['O'];
  $tOffset = $data['T'];

  if ($num == 0)
    return new Locale();

  $format = $format . ($num * 2);

  $o = $oOffset;
  $oTable = unpack($format, substr($f, $o, 8 * $num));
  $o = $tOffset;
  $tTable = unpack($format, substr($f, $o, 8 * $num));

  return array($num, $oTable, $tTable, $f);
}

function readMo6($file) {
  $f = fopen($file, 'r');

  if (!$f) {
    trigger_error('Could not open file: ' . $file, E_USER_ERROR);
    return null;
  }

  $magic = bin2hex(fread($f, 4));
  if ($magic === '950412de') { // Big endian
    $header = 'Nrev/NN/NO/NT/NS/NH';
    $format = 'N';
  }
  else if ($magic === 'de120495') { // Little endian
    $header = 'Vrev/VN/VO/VT/VS/VH';
    $format = 'V';
  }
  else {
    trigger_error('Not a valid MO file: incorrect magic number: ' . $magic, E_USER_ERROR);
    return null;
  }

  $data = unpack($header, fread($f, 6 * 4));
  $num = $data['N'];
  $oOffset = $data['O'];
  $tOffset = $data['T'];

  if ($num == 0)
    return new Locale();

  $format = $format . ($num * 2);

  fseek($f, $oOffset);
  $oTable = unpack($format, fread($f, 8 * $num));
  $tTable = unpack($format, fread($f, 8 * $num));

  return array($num, $oTable, $tTable, $f);
}

function getMo5(array $data, $needle) {
  $s = 0;
  list($e, $oTable, $tTable, $f) = $data;
  while ($s <= $e) {
    $m = $s + floor(($e - $s) / 2);
    $tOffset = $m * 2 + 1;
    $length = $oTable[$tOffset];
    $offset = $oTable[$tOffset + 1];
    if ($length == 0) {
      $message = '';
    }
    else { 
      $message = substr($f, $offset, $length);
      $nul = strpos($message, "\0");
      if ($nul !== false)
        $message = substr($message, $nul + 1); // gets plural
    }
    $comp = strcmp($needle, $message);
    if ($comp == 0) {
      $length = $tTable[$tOffset];
      $offset = $tTable[$tOffset + 1];
      return substr($f, $offset, $length);
    }
    else if ($comp < 0) {
      $e = $m - 1;
    }
    else {
      $s = $m + 1; 
    }
  }
  var_dump($s, $m, $e);
  var_dump($needle);
  var_dump($message);exit;
  trigger_error('msg not found', E_USER_ERROR);
}

function getMo6(array $data, $needle) {
  $s = 0;
  list($e, $oTable, $tTable, $f) = $data;
  while ($s <= $e) {
    $m = $s + floor(($e - $s) / 2);
    $tOffset = $m * 2 + 1;
    $length = $oTable[$tOffset];
    $offset = $oTable[$tOffset + 1];
    if ($length == 0) {
      $message = '';
    }
    else { 
      fseek($f, $offset);
      $message = fread($f, $length);
      $nul = strpos($message, "\0");
      if ($nul !== false)
        $message = substr($message, $nul + 1); // gets plural
    }
    $comp = strcmp($needle, $message);
    if ($comp == 0) {
      $length = $tTable[$tOffset];
      $offset = $tTable[$tOffset + 1];
      fseek($f, $offset);
      return fread($f, $length);
    }
    else if ($comp < 0) {
      $e = $m - 1;
    }
    else {
      $s = $m + 1; 
    }
  }
  trigger_error('msg not found', E_USER_ERROR);
}

function readAndGetMo5($file, $messages) {
  $data = readMo5($file);
  foreach ($messages as $message)
    getMo5($data, $message);
}

function readAndGetMo6($file, $messages) {
  $data = readMo6($file);
  foreach ($messages as $message)
    getMo6($data, $message);
}


function readAndGetMo7($file, $messages) {
  $l = Locale::readMo($file);
  foreach ($messages as $message)
    $l->get($message);
}

function readAndGetMo8($file, $messages) {
  $l = new LazyLocale($file);
  foreach ($messages as $message)
    $l->get($message);
}

function readMoSeri($file) {
  return unserialize(file_get_contents($file));
}

function readMoPhp($file) {
  return include $file;
}

function readMoJson($file) {
  return Json::decodeFile($file);
}

ini_set('display_errors', true);

include '../../LAB/LabTest.php';

$test = new LabTest('Locale reader performance test');

$rounds = 20;

$file1 = '../../jivoocms/app/languages/da.mo';
$file2 = '../../jivoocms/app/languages/da.po';
$file3 = '../../jivoocms/app/languages/da.s';
$file4 = '../../jivoocms/app/languages/da.php';
$file5 = '../../jivoocms/app/languages/da.json';

$l1 = $test->testFunction($rounds, '\Jivoo\Core\I18n\Locale::readMo', $file1);

$messages = $l1->getTranslationStrings();
file_put_contents($file3, serialize($messages));
file_put_contents($file4, '<?php ' . var_export($messages, true) . ';');
file_put_contents($file5, Json::encode($messages));

$l2 = $test->testFunction($rounds, 'readMo2', $file1);
$l3 = $test->testFunction($rounds, 'readMo3', $file1);
$l4 = $test->testFunction($rounds, 'readMo4', $file1);
$l5 = $test->testFunction($rounds, 'readMoSeri', $file3);
$l6 = $test->testFunction($rounds, 'readMoPhp', $file4);
$l7 = $test->testFunction($rounds, 'readMoJson', $file5);
// $l5 = $test->testFunction($rounds, 'readMo5', $file1);
// $l6 = $test->testFunction($rounds, 'readMo6', $file1);
$messages = array_keys($l1->getTranslationStrings());
$gets = 100;
shuffle($messages);
$needles = array_slice($messages, 0, $gets);
$g5 = $test->testFunction($rounds, 'readAndGetMo5', $file1, $needles);
$g6 = $test->testFunction($rounds, 'readAndGetMo6', $file1, $needles);
$g7 = $test->testFunction($rounds, 'readAndGetMo7', $file1, $needles);
$g8 = $test->testFunction($rounds, 'readAndGetMo8', $file1, $needles);
$l7 = $test->testFunction($rounds, '\Jivoo\Core\I18n\Locale::readPo', $file2);

// $rounds = 500;

// $g5 = $test->testFunction($rounds, 'getMo5', $l5, 'The user does not exist.');
// $g6 = $test->testFunction($rounds, 'getMo6', $l6, 'The user does not exist.');

$test->dump(\Jivoo\Core\Log\ErrorHandler::getInstance()->getLogger()->getLog());

$test->report();