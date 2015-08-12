<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Core\I18n\Locale;
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
   * @param string $scope Scope.
   * @param string $file File path.
   */
  public function scanFile($scope, $file) {
    $content = file_get_contents($scope . '/' . $file);
    $file = '../' . $file;
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
      if (!isset($this->sourceRefs[$message]))
        $this->sourceRefs[$message] = array();
      $this->sourceRefs[$message][] = array($file, $line);
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
      if (!isset($this->sourceRefs[$message]))
        $this->sourceRefs[$message] = array();
      $this->sourceRefs[$message][] = array($file, $line);
    }
  }
  
  /**
   * Scan a directory tree recursively.
   * @param string $scope Scope.
   * @param string $dir Directory path.
   */
  public function scanDir($scope, $dir = '') {
    $files = scandir($scope . '/' . $dir);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.')
          continue;
        if ($dir != '')
          $file = $dir . '/' . $file;
        $path = $scope . '/' . $file;
        if (is_dir($path)) {
          $this->scanDir($scope, $file);
        }
        else {
          if (preg_match('/\.php$/', $file) === 1)
            $this->scanFile($scope, $file);
        }
      }
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
   * @return Locale Localization.
   */
  public function getLocalization() {
    $l = new Locale();
    foreach ($this->stringLiterals as $message => $literal)
      $l->set($message, $message);
    foreach ($this->pluralLiterals as $message => $array) {
      list($plural, $singular, $smessage) = $array;
      $l->set($message, array($smessage, $message));
    }
    return $l;
  }
  
  /**
   * Create a PHP file that returns a {@see Locale} object for the Core
   * language.
   * @return string PHP file content.
   */
  public function createCorePhpFile() {
    $php = '<?php' . PHP_EOL;
    foreach ($this->warnings as $warning)
      $php .= '// [WARNING] ' . $warning . PHP_EOL;
    $php .= PHP_EOL;
    $php .= '$l = new \Jivoo\Core\I18n\Locale()';
    $php .= ';' . PHP_EOL . PHP_EOL;
    $php .= '$l->name = "English";' . PHP_EOL;
    $php .= '$l->localName = "English";' . PHP_EOL;
    $php .= PHP_EOL;

    foreach ($this->stringLiterals as $message => $literal) {
      foreach ($this->sourceRefs[$message] as $source)
        $php .= '//: ' . implode(':', $source) . PHP_EOL; 
      $php .= '$l->set(' . PHP_EOL
        . '  ' . $literal . ',' . PHP_EOL
        . '  ' . $literal . ');' . PHP_EOL;
    }

    foreach ($this->pluralLiterals as $message => $array) {
      list($plural, $singular, $smessage) = $array;
      foreach ($this->sourceRefs[$message] as $source)
        $php .= '//: ' . implode(':', $source) . PHP_EOL;
      $php .= '$l->set(' . PHP_EOL
      . '  ' . $plural . ',' . PHP_EOL
      . '  array(' . $singular . ',' . PHP_EOL
      . '        ' . $plural . '));' . PHP_EOL;
    }

    $php .= PHP_EOL;
    $php .= 'return $l;' . PHP_EOL;
    return $php;
  }
  
  /**
   * @param string $string
   * @return string
   */
  private function quote($string) {
    return '"' . addcslashes($string, "\\\"\0..\37") . '"';
  }
  
  /**
   * Create a gettext POT-file.
   * @return string POT file content.
   */
  public function createPotFile($project = '') {
    $pot = 'msgid ""' . PHP_EOL
         . 'msgstr ""' . PHP_EOL
         . '"Project-Id-Version: ' . $project . '\n"' . PHP_EOL
         . '"POT-Creation-Date: ' . date('Y-m-d H:iO') . '\n"' . PHP_EOL
         . '"Language: en\n"' . PHP_EOL
         . '"MIME-Version: 1.0\n"' . PHP_EOL
         . '"Content-Type: text/plain; charset=UTF-8\n"' . PHP_EOL
         . '"Content-Transfer-Encoding: 8bit\n"' . PHP_EOL
         . '"X-Generator: Jivoo ' . \Jivoo\VERSION . '\n"' . PHP_EOL
         . '"Plural-Forms: nplurals=2; plural=(n != 1);\n"' . PHP_EOL . PHP_EOL;
    foreach ($this->stringLiterals as $message => $literal) {
      foreach ($this->sourceRefs[$message] as $source)
        $pot .= '#: ' . implode(':', $source) . PHP_EOL;
      $pot .= 'msgid ' . $this->quote($message) . PHP_EOL;
      $pot .= 'msgstr ""' . PHP_EOL . PHP_EOL;
    }
    foreach ($this->pluralLiterals as $message => $array) {
      list($plural, $singular, $smessage) = $array;
      foreach ($this->sourceRefs[$message] as $source)
        $pot .= '#: ' . implode(':', $source) . PHP_EOL;
      $pot .= 'msgid ' . $this->quote($smessage) . PHP_EOL;
      $pot .= 'msgid_plural ' . $this->quote($message) . PHP_EOL;
      $pot .= 'msgstr[0] ""' . PHP_EOL;
      $pot .= 'msgstr[1] ""' . PHP_EOL . PHP_EOL;
    }
    return $pot;
  }
}