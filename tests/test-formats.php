<?php
include('../lib/Jivoo/Core/bootstrap.php');

class ContentExtensions {


  private function replace($matches) {
    $function = $matches[1];
    preg_match_all('/\s+(?:([a-z]+)=)?"((?:[^\\\\"]|\\\\.)*)"/im', $matches[2], $parameters);
    for ($i = 0; $i < count($parameters[0]); $i++) {
      echo $parameters[1][$i] . '=';
      var_dump($parameters[2][$i]);
    }

  }
  
  public function compile($content) {
    return preg_replace_callback(
      '/\{\{\s*([a-z]+)((?:\s+([a-z]+=)?"(?:[^\\\\"]|\\\\.)*")*)\s*\}\}/im',
      array($this, 'replace'), $content
    );
  }
}

echo '<pre>';

$content = 'Edit the Expression & Text to see matches. Roll over matches or
the expression for details. Undo mistakes with ctrl-z. Save & Share expressions
with friends or the Community. A full Reference & Help is available
in {{link "Posts::view::15"}}, or {{break}} watch the video Tutorial.

{{figure file="test.png" caption="An image
with a \\"multiline\\" caption"}}

Sample text for testing:';

$compiler = new ContentExtensions;

$compiler->compile($content);

var_dump(Logger::getLog());

echo '</pre>';
