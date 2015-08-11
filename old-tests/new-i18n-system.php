<?php
use Jivoo\Core\I18n\Locale;
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
    $offsets[$i]['translation'] = unpack($row, fread($f, $o, 8));;
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

ini_set('display_errors', true);

include '../../LAB/LabTest.php';

$test = new LabTest('Locale reader');

$rounds = 20;

$file1 = '../../jivoocms/app/languages/da.mo';
$file2 = '../../jivoocms/app/languages/da.po';

$l1 = $test->testFunction($rounds, '\Jivoo\Core\I18n\Locale::readMo', $file1);
$l2 = $test->testFunction($rounds, 'readMo2', $file1);
$l3 = $test->testFunction($rounds, '\Jivoo\Core\I18n\Locale::readPo', $file2);

$test->report();