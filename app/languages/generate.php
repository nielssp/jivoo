#!/usr/bin/php
<?php
if (count($argv) < 2) {
  echo 'usage: ' . $argv[0] . ' ROOT [STYLE]' . PHP_EOL;
  exit(1);
}
$root = $argv[1];
$style = 1;
if (isset($argv[2]))
  $style = $argv[2];

$stringLiterals = array();
$pluralLiterals = array();

function scan_file($file) {
  global $stringLiterals;
  global $pluralLiterals;
  $content = file_get_contents($file);
  preg_match_all('/\btr\(/', $content, $matchesTest, PREG_OFFSET_CAPTURE);
  preg_match_all('/\btr\(\s*(\'([^\'\\\\]|\\\\.)*\'|"([^"\\\\]|\\\\.)*")/s', $content, $matches, PREG_OFFSET_CAPTURE);
  if (count($matchesTest[0]) != count($matches[0])) {
    $offsets = array();
    foreach ($matches[0] as $match) {
      $offsets[$match[1]] = true;
    }
    foreach ($matchesTest[0] as $match) {
      $offset = $match[1];
      if (!isset($offsets[$offset])) {
        echo '// [INFO] invalid tr() at offset ' . $offset . ' in ' . $file . PHP_EOL;
      }
    }
  }
  foreach($matches[1] as $match) {
    $stringLiteral = $match[0];
    $message = eval('return ' . $stringLiteral . ';'); 
    $stringLiterals[$message] = $stringLiteral;
  }
  preg_match_all('/\btn\(/', $content, $matchesTest, PREG_OFFSET_CAPTURE);
  preg_match_all('/\btn\(\s*(\'([^\'\\\\]|\\\\.)*\'|"([^"\\\\]|\\\\.)*")\s*,\s*(\'([^\'\\\\]|\\\\.)*\'|"([^"\\\\]|\\\\.)*")/s', $content, $matches, PREG_OFFSET_CAPTURE);
  if (count($matchesTest[0]) != count($matches[0])) {
    $offsets = array();
    foreach ($matches[0] as $match) {
      $offsets[$match[1]] = true;
    }
    foreach ($matchesTest[0] as $match) {
      $offset = $match[1];
      if (!isset($offsets[$offset])) {
        echo '// [INFO] invalid tn() at offset ' . $offset . ' in ' . $file . PHP_EOL;
      }
    }
  }
  $numMatches = count($matches[1]);
  for ($i = 0; $i < $numMatches; $i++) {
    $pluralLiteral = $matches[1][$i][0];
    $singularLiteral = $matches[4][$i][0];
    $message = eval('return ' . $pluralLiteral . ';');
    $pluralLiterals[$message] = array($pluralLiteral, $singularLiteral);
  }
}

function scan_dir($dirPath) {
  $dir = opendir($dirPath);
  if ($dir) {
    while (($file = readdir($dir)) !== false) {
      if ($file[0] == '.')
        continue;
      $file = $dirPath . '/' . $file;
      if (is_dir($file)) {
        scan_dir($file);
      }
      else {
        $ext = explode('.', $file);
        if ($ext[count($ext) - 1] == 'php') {
          scan_file($file);
        }
      }
    }
    closedir($dir);
  }
}

if ($style != 0)
  echo '<?php' . PHP_EOL;

scan_dir($root);

if ($style == 0)
  exit;

echo '$l = new Localization();' . PHP_EOL . PHP_EOL;

if ($style == 3) {
  echo '$l->set(array(' . PHP_EOL . ' ';
}

foreach ($stringLiterals as $literal) {
  if ($style == 2) {
    echo '$l->set( ' . $literal . PHP_EOL
      . '       , ' . $literal . PHP_EOL
      . '       );' . PHP_EOL;
  }
  else if ($style == 3) {
    echo ' array(' . PHP_EOL
      . '    ' . $literal . ',' . PHP_EOL
      . '    ' . $literal . ',' . PHP_EOL
      . '  ),';
  }
  else {
    echo '$l->set(' . PHP_EOL
      . '  ' . $literal . ',' . PHP_EOL
      . '  ' . $literal . PHP_EOL
      . ');' . PHP_EOL;
  }
}
foreach ($pluralLiterals as $array) {
  list($plural, $singular) = $array;
  if ($style == 2) {
    echo '$l->set( ' . $plural . PHP_EOL
      . '       , ' . $singular . PHP_EOL
      . "       , '/^-?1$/'" . PHP_EOL
      . '       );' . PHP_EOL;
    echo '$l->set( ' . $plural . PHP_EOL
      . '       , ' . $plural . PHP_EOL
      . '       );' . PHP_EOL;
  }
  else if ($style == 3) {
    echo ' array(' . PHP_EOL
      . '    ' . $plural . ',' . PHP_EOL
      . '    ' . $singular . ',' . PHP_EOL
      . "    '/^-?1$/'," . PHP_EOL
      . '  ),';
    echo ' array(' . PHP_EOL
      . '    ' . $plural . ',' . PHP_EOL
      . '    ' . $plural . ',' . PHP_EOL
      . '  ),';
  }
  else {
    echo '$l->set(' . PHP_EOL
      . '  ' . $plural . ',' . PHP_EOL
      . '  ' . $singular . ',' . PHP_EOL
      . "  '/^-?1$/'" . PHP_EOL
      . ');' . PHP_EOL;
    echo '$l->set(' . PHP_EOL
      . '  ' . $plural . ',' . PHP_EOL
      . '  ' . $plural . PHP_EOL
      . ');' . PHP_EOL;
  }
}

if ($style == 3) {
  echo PHP_EOL . ');';
}

echo PHP_EOL;
echo 'return $l;' . PHP_EOL;
