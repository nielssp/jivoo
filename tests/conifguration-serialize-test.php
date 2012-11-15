<?php
function var_import($data) {
  return eval('return ' . $data . ';');
}

function json_pretty_printer($data, $prefix = '') {
  $json = '{' . PHP_EOL;
  foreach ($data as $key => $value) {
    $json .= $prefix . '  ' . $key . ': ';
    if (is_array($value)) {
      $json .= json_pretty_printer($value, $prefix . '  ');
    }
    else {
      $json .= var_export($value, true);
    }
    $json .= ',' . PHP_EOL;
  }
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

function read_config() {
  return include '../cfg/test-config.cfg.php';
}

include '../app/essentials.php';
include '../../LAB/LabTest.php';

$fileData = explode('?>', file_get_contents(p(CFG . 'config.cfg.php')));
$testData = Configuration::parseData($fileData[1]);  

$rounds = 50;

$test = new LabTest('Configuration serialization');
$json_encoded = $test->testFunction($rounds, 'json_encode', $testData);
$test->dumpResult();
$test->testFunction($rounds, 'json_pretty_printer', $testData);
$test->dumpResult();
$file = fopen("../cfg/test-config.json", "w");
fwrite($file, $json_encoded);
fclose($file);
$php_encoded = $test->testFunction($rounds, 'php_pretty_printer', $testData);
$file = fopen("../cfg/test-config.cfg.php", "w");
fwrite($file, "<?php\nreturn " . $php_encoded . ";\n");
fclose($file);
$test->dumpResult();
$seri_encoded = $test->testFunction($rounds, array('Configuration', 'compileData'), $testData);
$test->dumpResult();
$exported = $test->testFunction($rounds, 'var_export', $testData, true);
$test->dumpResult();
$test->testFunction($rounds, 'json_decode', $json_encoded);
$test->dumpResult();
$test->testFunction($rounds, array('Configuration', 'parseData'), $seri_encoded);
$test->dumpResult();
$test->testFunction($rounds, 'var_import', $exported);
$test->dumpResult();
$test->testFunction($rounds, 'read_config');
$test->dumpResult();

$test->report();

