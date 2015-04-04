<?php

class Dir implements Iterator {

  private $path;
  private $dir = false;
  private $current = false;
  
  public function __construct($path) {
    $this->path = $path;
    $this->open();
  }
  
  private function open() {
    $this->dir = opendir($this->path);
    if (!$this->dir)
      throw new Exception('Could not open directory');
    $this->current = readdir($this->dir);
  }
  
  public function close() {
    closedir($this->dir);
  }
  
  public function current() {
    return $this->current;
  }
  
  public function next() {
    $this->current = readdir($this->dir);
  }
  
  public function key() {
    return null;
  }
  
  public function valid() {
    return $this->current !== false;
  }
  
  public function rewind() {
  }
}

class Dir2 implements Iterator {
  
  private $files;
  
  public function __construct($path) {
    $this->files = scandir($path);
  }
  
  public function current() {
    return current($this->files);
  }
  
  public function next() {
    next($this->files);
  }
  
  public function key() {
    return key($this->files);
  }
  
  public function valid() {
    return key($this->files) !== null;
  }
  
  public function rewind() {
    reset($this->files);
  }
}


function usingReadDir() {
  $dir = opendir('.');
  $files = '';
  while (($file = readdir($dir)) !== false) {
    $files .= $file;
  }
  closedir($dir);
  return $files;
}

function usingScanDir() {
  $files = '';
  foreach (scandir('.') as $file) {
    $files .= $file;
  }
  return $files;
}

function usingScanDir2() {
  return implode('', scandir('.'));
}

function usingDir() {
  $files = '';
  $dir = new Dir('.');
  foreach ($dir as $file) {
    $files .= $file;
  }
  return $files;
}

function usingDir2() {
  $files = '';
  $dir = new Dir2('.');
  foreach ($dir as $file) {
    $files .= $file;
  }
  return $files;
}

function scandir2($dir) {
  $dir = opendir($dir);
  if ($dir) {
    $files = array();
    while (($file = readdir($dir)) !== false) {
      $files[] = $file;
    }
    closedir($dir);
    return $files;
  }
  return false;
}

include '../../LAB/LabTest.php';

$test = new LabTest('Directory traversal');

$rounds = 100;

$test->testFunction($rounds, 'usingReadDir');

$test->testFunction($rounds, 'usingScanDir2');

$test->testFunction($rounds, 'usingScanDir');

$test->testFunction($rounds, 'usingReadDir');

$test->testFunction($rounds, 'usingDir');
$test->testFunction($rounds, 'usingDir2');

$test->testFunction($rounds, 'scandir', '.');

$test->testFunction($rounds, 'scandir2', '.');


$test->report();

