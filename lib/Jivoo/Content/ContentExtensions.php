<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

/**
 * A collection of content extensions.
 * @todo One style for both inline and block.
 */
class ContentExtensions {
  /**
   * @var string Reglar expression used to match inline extensions.
   */
  const INLINE_REGEX = '/\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}/im';
  
  /**
   * @var string Regular expression used to match block extensions.
   */
  const BLOCK_REGEX = '/(?:<p>\s*)\{\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}\}(?:\s*<\/p>)/im';

  /**
   * @var array Extension functions.
   */
  private $functions = array();
  
  /**
   * Add a content extension.
   * @param string $function Name of extension.
   * @param array $parameters Associative array of default paramters.
   * @param callback $callback Callback for extension.
   */
  public function add($function, $parameters, $callback) {
    $this->functions[$function] = array(
      'defaults' => $parameters,
      'parameters' => array_keys($parameters),
      'callback' => $callback
    );
  }

  /**
   * Replace extension.
   * @param array $matches Regex matches.
   * @return string Extension replacement.
   */
  private function replace($matches) {
    $function = $matches[1];
    if (!isset($this->functions[$function]))
      return $matches[0];
    preg_match_all('/\s+(?:([a-z]+)=)?"((?:[^\\\\"]|\\\\.)*)"/im', $matches[2], $parameterMatches);
    $unnamedCount = 0;
    $formalParameters = $this->functions[$function]['parameters'];
    $actualParameters = $this->functions[$function]['defaults'];
    $numParameters = count($parameterMatches[0]);
    for ($i = 0; $i < $numParameters; $i++) {
      if (empty($parameterMatches[1][$i])) {
        if (!isset($formalParameters[$unnamedCount]))
          continue;
        $actualParameters[$formalParameters[$unnamedCount]] = $parameterMatches[2][$i];
        $unnamedCount++;
      }
      else {
        $actualParameters[$parameterMatches[1][$i]] = $parameterMatches[2][$i];
      }
    }
    return call_user_func(
      $this->functions[$function]['callback'],
      $actualParameters
    ); 
  }
  
  /**
   * Apply extensions to content.
   * @param string $content Content.
   * @return string Result.
   */
  public function compile($content) {
    return preg_replace_callback(
      self::INLINE_REGEX,
      array($this, 'replace'),
      preg_replace_callback(
        self::BLOCK_REGEX,
        array($this, 'replace'),
        $content
      )
    );
  }
}