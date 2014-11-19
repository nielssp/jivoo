<?php
class ContentExtensions {

  const INLINE_REGEX = '/\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}/im';
  const BLOCK_REGEX = '/(?:<p>\s*)\{\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}\}(?:\s*<\/p>)/im';

  private $functions = array();
  
  public function add($function, $parameters, $callback) {
    $this->functions[$function] = array(
      'defaults' => $parameters,
      'parameters' => array_keys($parameters),
      'callback' => $callback
    );
  }

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