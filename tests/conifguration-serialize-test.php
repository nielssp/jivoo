<?php
function var_import($data) {
  return eval('return ' . $data . ';');
}

function json_pretty_printer($data, $prefix = '') {
  $json = '{' . PHP_EOL;
  $lines = array();
  foreach ($data as $key => $value) {
    $line = $prefix . '  "' . addslashes($key) . '": ';
    if (is_array($value)) {
      $line .= json_pretty_printer($value, $prefix . '  ');
    }
    else if (is_string($value)) {
      $line .= '"' . addslashes($value) . '"';
    }
    else {
      $line .= var_export($value, true);
    }
    $lines[] = $line;
  }
  $json .= implode(',' . PHP_EOL, $lines) . PHP_EOL;
  return $json . $prefix . '}';
}

function php_pretty_printer($data, $prefix = '') {
  $json = 'array(' . PHP_EOL;
  foreach ($data as $key => $value) {
    $json .= $prefix . '  ' . var_export($key, true) . ' => ';
    if (is_array($value)) {
      $json .= php_pretty_printer($value, $prefix . '  ');
    }
    else {
      $json .= var_export($value, true);
    }
    $json .= ',' . PHP_EOL;
  }
  return $json . $prefix . ')';
}

function include_config() {
  return include './config/test-config.php';
}
function include_config2() {
  return include '../app/jivoo/app.php';
}

function eval_config() {
  $content = file_get_contents('./config/test-config.php');
  $content = str_replace('<?php', '', $content);
  return eval($content);
}

function eval_config2() {
  $content = file_get_contents('../app/jivoo/app.php');
  $content = str_replace('<?php', '', $content);
  return eval($content);
}

function decode_config() {
  $content = file_get_contents('./config/test-config.json');
  return json_decode($content, true);
}
function decode_config2() {
  $content = file_get_contents('../app/jivoo/app.json');
  return json_decode($content, true);
}

function serialize_config($data) {
  file_put_contents('config/cached', serialize($data));
}

function unserialize_config() {
  return unserialize(file_get_contents('config/cached'));
}

function serialize_config2($data) {
  file_put_contents('config/cached2.php', '<?php return ' . var_export($data, true) . ';');
}

function unserialize_config2() {
  return include 'config/cached2.php';
}

function decode_yaml_config() {
  return spyc_load_file('./config/test-config.yaml');
}

function new_appconfig() {
  $config = new AppConfig('./config/test-config.php');
  return $config;
}

include '../lib/Jivoo/Core/bootstrap.php';
include '../../LAB/LabTest.php';

include './spyc-master/spyc.php';


$config = new AppConfig('../user/jivoo/config.php');
$testData = $config->getArray();

$rounds = 50;


$test = new LabTest('Configuration serialization');
$test->dump($testData);
$json_encoded = $test->testFunction($rounds, 'json_encode', $testData);
$test->dumpResult();
$json_encoded = $test->testFunction($rounds, 'json_pretty_printer', $testData);
$test->dumpResult();
$file = fopen("./config/test-config.json", "w");
fwrite($file, $json_encoded);
fclose($file);
$php_encoded = $test->testFunction($rounds, 'php_pretty_printer', $testData);
$file = fopen("./config/test-config.php", "w");
fwrite($file, "<?php\nreturn " . $php_encoded . ";\n");
fclose($file);
$test->dumpResult();
$yaml_encoded = $test->testFunction($rounds, 'spyc_dump', $testData);
$test->dumpResult();
$file = fopen('./config/test-config.yaml', 'w');
fwrite($file, $yaml_encoded);
fclose($file);
$exported = $test->testFunction($rounds, 'var_export', $testData, true);
$test->dumpResult();
$test->testFunction($rounds, 'json_decode', $json_encoded, true);
$test->dumpResult();
$test->testFunction($rounds, 'var_import', $exported);
$test->dumpResult();
$test->testFunction($rounds, 'new_appconfig');
$r1 = $test->testFunction($rounds, 'include_config');
$r2 = $test->testFunction($rounds, 'eval_config');
$r3 = $test->testFunction($rounds, 'decode_config');
$r4 = $test->testFunction($rounds, 'decode_yaml_config');
$test->testFunction($rounds, 'include_config2');
$test->testFunction($rounds, 'eval_config2');
$test->testFunction($rounds, 'decode_config2');
$test->testFunction($rounds, 'serialize_config', $testData);
$test->testFunction($rounds, 'unserialize_config');
$test->testFunction($rounds, 'serialize_config2', $testData);
$test->testFunction($rounds, 'unserialize_config2');
$test->dump($r1 == $r2, 'r1 == r2');
$test->dump($r2 == $r3, 'r2 == r3');
$test->dump($r3 == $r4, 'r3 == r4');

$test->report();

