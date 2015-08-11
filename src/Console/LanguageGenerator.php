<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Core\Localization;
use Jivoo\Core\Json;

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
  
  private $sourceRefs = array();
  
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
      $line = substr_count($content, "\n", 0, $match[1]) + 1;
      $this->sourceRefs[$message] = array($file, $line);
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
      $smessage = eval('return ' . $singularLiteral . ';');
      $this->pluralLiterals[$message] = array($pluralLiteral, $singularLiteral, $smessage);
      $line = substr_count($content, "\n", 0, $matches[0][$i][1]) + 1;
      $this->sourceRefs[$message] = array($file, $line);
    }
  }
  
  /**
   * Scan a directory tree recursively.
   * @param string $dir Directory path.
   */
  public function scanDir($dir) {
    $files = scandir($dir);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.')
          continue;
        $file = $dir . '/' . $file;
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
  
  /**
   * Create a PHP file that returns a {@see Localization} object for the Core
   * language.
   * @return string PHP file content.
   */
  public function createCorePhpFile() {
    $php = '<?php' . PHP_EOL;
    foreach ($this->warnings as $warning)
      $php .= '// [WARNING] ' . $warning . PHP_EOL;
    $php .= PHP_EOL;
    $php .= '$l = new \Jivoo\Core\Localization()';
    $php .= ';' . PHP_EOL . PHP_EOL;
    $php .= '$l->name = "English";' . PHP_EOL;
    $php .= '$l->localName = "English";' . PHP_EOL;
    $php .= PHP_EOL;

    foreach ($this->stringLiterals as $message => $literal) {
      $php .= '//: ' . implode(':', $this->sourceRefs[$message]) . PHP_EOL; 
      $php .= '$l->set(' . PHP_EOL
        . '  ' . $literal . ',' . PHP_EOL
        . '  ' . $literal . PHP_EOL
        . ');' . PHP_EOL;
    }

    foreach ($this->pluralLiterals as $message => $array) {
      list($plural, $singular, $smessage) = $array;
      $php .= '//: ' . implode(':', $this->sourceRefs[$message]) . PHP_EOL;
      $php .= '$l->set(' . PHP_EOL
      . '  ' . $plural . ',' . PHP_EOL
      . '  ' . $singular . ',' . PHP_EOL
      . "  '/^-?1$/'" . PHP_EOL
      . ');' . PHP_EOL;
      $php .= '$l->set(' . PHP_EOL
      . '  ' . $plural . ',' . PHP_EOL
      . '  ' . $plural . PHP_EOL
      . ');' . PHP_EOL;
    }

    $php .= PHP_EOL;
    $php .= 'return $l;' . PHP_EOL;
    return $php;
  }
  
  /**
   * Create a gettext POT-file.
   * @return string POT file content.
   */
  public function createPotFile() {
    $pot = '';
    foreach ($this->stringLiterals as $message => $literal) {
      $pot .= '#: ' . implode(':', $this->sourceRefs[$message]) . PHP_EOL;
      $pot .= 'msgid ' . Json::encode($message) . PHP_EOL;
      $pot .= 'msgstr ' . Json::encode($message) . PHP_EOL . PHP_EOL;
    }
    foreach ($this->pluralLiterals as $message => $array) {
      list($plural, $singular, $smessage) = $array;
      $pot .= '#: ' . implode(':', $this->sourceRefs[$message]) . PHP_EOL;
      $pot .= 'msgid ' . Json::encode($smessage) . PHP_EOL;
      $pot .= 'msgid_plural ' . Json::encode($message) . PHP_EOL;
      $pot .= 'msgstr[0] ' . Json::encode($smessage) . PHP_EOL;
      $pot .= 'msgstr[1] ' . Json::encode($message) . PHP_EOL . PHP_EOL;
    }
    return $pot;
  }
}