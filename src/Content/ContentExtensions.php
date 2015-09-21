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
   * @var string Regular expression used to match block extensions.
   */
  const REGEX = '/(<p>\s*)?\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}(\s*<\/p>)?/im';

  /**
   * @var array Extension functions.
   */
  private $functions = array();
  
  /**
   * Add a inline content extension.
   * @param string $function Name of extension.
   * @param array $parameters Associative array of default paramters.
   * @param callback $callback Callback for extension.
   */
  public function inline($function, $parameters, $callback) {
    $this->functions[$function] = array(
      'defaults' => $parameters,
      'parameters' => array_keys($parameters),
      'callback' => $callback,
      'inline' => true
    );
  }
  
  /**
   * Add a block content extension.
   * @param string $function Name of extension.
   * @param array $parameters Associative array of default paramters.
   * @param callback $callback Callback for extension.
   */
  public function block($function, $parameters, $callback) {
    $this->functions[$function] = array(
      'defaults' => $parameters,
      'parameters' => array_keys($parameters),
      'callback' => $callback,
      'inline' => false
    );
  }

  /**
   * Replace extension.
   * @param array $matches Regex matches.
   * @return string Extension replacement.
   */
  private function replace($matches) {
    $block = ($matches[1] != '' and isset($matches[4]));
    $function = $matches[2];
    if (!isset($this->functions[$function]))
      return $matches[0];
    preg_match_all('/\s+(?:([a-z]+)=)?"((?:[^\\\\"]|\\\\.)*)"/im', $matches[3], $parameterMatches);
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
    if (!$this->functions[$function]['inline'] and $block) {
      $matches[1] = '';
      $matches[4] = '';
    }
    return $matches[1] . call_user_func(
      $this->functions[$function]['callback'],
      $actualParameters
    ) . $matches[4]; 
  }
  
  /**
   * Apply extensions to content.
   * @param string $content Content.
   * @return string Result.
   */
  public function compile($content) {
    return preg_replace_callback(
      self::REGEX,
      array($this, 'replace'),
      $content
    );
  }
  
  /**
   * Insert link for route.
   * @param array $params Content extension parameters.
   * @return string Link.
   */
  public static function linkFunction($params) {
    try {
      return $this->m->Routing->getLink($params['route']);
    }
    catch (InvalidRouteException $e) {
      return 'invalid link';
    }
  }
  
  /**
   * Create a break between summary and full content.
   * @param array $params Content extension parameters.
   * @return string Break div.
   */
  public static function breakFunction($params) {
    return '<div class="break"></div>';
  }

  /**
   * Create a page break.
   * @param array $params Content extension parameters.
   * @return string Page break div.
   */
  public static function pageBreakFunction($params) {
    return '<div class="page-break"></div>';
  }

  /**
   * Name the current content page.
   * @param array $params Content extension parameters.
   * @return string Page name div.
   */
  public static function pageFunction($params) {
    if (isset($params['name']))
      return '<div class="page-name" data-name="' . h($params['name']) . '"></div>';
    else
      return '<div class="page-name"></div>';
  }
}