<?php
include('../lib/Jivoo/Core/bootstrap.php');

class ContentExtensions {

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
      '/\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}/im',
      array($this, 'replace'), $content
    );
  }
}

function mydate($params) {
  return date($params['format']);
}

function figure($params) {
  var_dump($params);
  return '<div class="figure"><img src="' . $params['file'] . '" />
    <div class="caption">' . $params['caption'] . '</div></div>';
}

echo '<pre>';

$content = 'Edit the Expression & Text to see matches. Roll over matches or
the expression for details. Undo mistakes with ctrl-z. Save & Share expressions
with friends or the Community. A full Reference & Help is available
in {{link "Posts::view::15"}}, or {{break}} watch the video Tutorial.

{{figure file="test.png" caption="An image
with a \\"multiline\\" caption"}}

The current time is {{date "Y-m-d H:i:s"}}.
  
Sample text for testing:';

$compiler = new ContentExtensions;

$compiler->add('date', array('format' => 'Y-m-d'), 'mydate');
$compiler->add('figure', array('file' => null, 'caption' => null), 'figure');

var_dump($compiler->compile($content));

var_dump(Logger::getLog());

echo '</pre>';
