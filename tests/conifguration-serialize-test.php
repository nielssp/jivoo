<?php
function utimer($start) {
  return round((microtime(true) - $start)*1000000);
}

include '../app/essentials.php';

$fileData = explode('?>', file_get_contents(p(CFG . 'config.cfg.php')));
$testData = Configuration::parseData($fileData[1]);  

$rounds = 10;
?>
<p>Experiment #1 (json_encode())<br/>
<?php
$t_start = microtime(true);

for ($i = 0; $i < $rounds; $i++) {
  $json_encoded = json_encode($testData, JSON_PRETTY_PRINT);
}
echo $json_encoded;
echo '<br/>';
echo 'Length: ' . strlen($json_encoded) . '<br/>';
echo utimer($t_start) . ' µs';
?></p>

<p>Experiment #2 (Configuration::compileData())<br/>
<?php
$t_start = microtime(true);

for ($i = 0; $i < $rounds; $i++) {
  $conf_encoded = Configuration::compileData($testData);
}
echo $conf_encoded;
echo '<br/>';
echo 'Length: ' . strlen($conf_encoded) . '<br/>';
echo utimer($t_start) . ' µs';
?></p>

<p>Experiment #3 (json_decode())<br/>
<?php
$t_start = microtime(true);

for ($i = 0; $i < $rounds; $i++) {
  $testData = json_decode($json_encoded);
}
echo '<br/>';
echo utimer($t_start) . ' µs';
?></p>

<p>Experiment #4 (Configuration::parseData())<br/>
<?php
$t_start = microtime(true);

for ($i = 0; $i < $rounds; $i++) {
  $testData = Configuration::parseData($conf_encoded);
}
echo '<br/>';
echo utimer($t_start) . ' µs';
?></p>
