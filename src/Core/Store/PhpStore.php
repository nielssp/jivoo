<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Stores data as PHP files. 
 */
class PhpStore extends FileStore {
  /**
   * {@inheritdoc}
   */
  protected $defaultContent = "<?php\nreturn array();";
  
  /**
   * {@inheritdoc}
   */
  protected function encode(array $data) {
    $data = self::prettyPrint($data);
    return '<?php' . PHP_EOL . 'return ' . $data . ';' . PHP_EOL;
  }
  
  /**
   * {@inheritdoc}
   */
  protected function decode($content) {
    if (substr($content, 0, 5) !== '<?php')
      throw new AccessException(tr('Invalid file format'));
    return eval(substr($content, 5));
  }
  
  /**
   * Create valid PHP array representation
   * @param array $data Associative array
   * @param string $prefix Prefix to put in front of new lines
   * @return string PHP source
   */
  public static function prettyPrint($data, $prefix = '') {
    $php = 'array(' . PHP_EOL;
    if (is_array($data) and array_diff_key($data, array_keys(array_keys($data)))) {
      foreach ($data as $key => $value) {
        $php .= $prefix . '  ' . var_export($key, true) . ' => ';
        if (is_array($value)) {
          $php .= self::prettyPrint($value, $prefix . '  ');
        }
        else {
          $php .= var_export($value, true);
        }
        $php .= ',' . PHP_EOL;
      }
    }
    else {
      foreach ($data as $value) {
        $php .= $prefix . '  ';
        if (is_array($value)) {
          $php .= self::prettyPrint($value, $prefix . '  ');
        }
        else {
          $php .= var_export($value, true);
        }
        $php .= ',' . PHP_EOL;
      }
    }
    return $php . $prefix . ')';
  }
}