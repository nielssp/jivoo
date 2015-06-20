<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Core\Localization;

/**
 * Finds, and generates a localization from, occurrences of {@see tr()) and
 * {@see tn()} in files or directories.
 */
class LanguageGenerator {

  /**
   * @var string[]
   */
  private $stringLiterals = array();

  /**
   * @var array[]
   */
  private $pluralLiterals = array();
  
  /**
   * @var string[]
   */
  private $warnings = array();
  
  /**
   * Scan a single file.
   * @param string $file File path.
   */
  public function scanFile($file) {
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
          $line = substr_count($content, "\n", 0, $offset) + 1;
          $this->warnings[] = tr('Invalid use of %1 on line %2 in %3', 'tr()', $line, $file);
        }
      }
    }
    foreach($matches[1] as $match) {
      $stringLiteral = $match[0];
      $message = eval('return ' . $stringLiteral . ';');
      $this->stringLiterals[$message] = $stringLiteral;
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
          $line = substr_count($content, "\n", 0, $offset) + 1;
          $this->warnings[] = tr('Invalid use of %1 on line %2 in %3', 'tn()', $line, $file);
        }
      }
    }
    $numMatches = count($matches[1]);
    for ($i = 0; $i < $numMatches; $i++) {
      $pluralLiteral = $matches[1][$i][0];
      $singularLiteral = $matches[4][$i][0];
      $message = eval('return ' . $pluralLiteral . ';');
      $this->pluralLiterals[$message] = array($pluralLiteral, $singularLiteral);
    }
  }
  
  /**
   * Scan a directory tree recursively.
   * @param string $dirPath Directory path.
   */
  public function scanDir($dirPath) {
    $dir = opendir($dirPath);
    if ($dir) {
      while (($file = readdir($dir)) !== false) {
        if ($file[0] == '.')
          continue;
        $file = $dirPath . '/' . $file;
        if (is_dir($file)) {
          $this->scanDir($file);
        }
        else {
          $ext = explode('.', $file);
          if ($ext[count($ext) - 1] == 'php') {
            $this->scanFile($file);
          }
        }
      }
      closedir($dir);
    }
  }
  
  /**
   * Get list of warnings generated when scanning files.
   * @return string[] List of warnings.
   */
  public function getWarnings() {
    return $this->warnings;
  }
  
  /**
   * Create a localization object from the scanned strings.
   * @return Localization Localization.
   */
  public function getLocalization() {
    $l = new Localization();
    foreach ($this->stringLiterals as $literal)
      $l->set($literal, $literal);
    foreach ($this->pluralLiterals as $array) {
      list($plural, $singular) = $array;
      $l->set($plural, $singular, '/^-?1$/');
      $l->set($plural, $plural);
    }
    return $l;
  }
}