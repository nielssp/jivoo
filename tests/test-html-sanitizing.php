<?php

class Format {
  private $allow = array();

  function replaceTags($matches) {
    var_dump($matches);
    $num = count($matches);
    $tag = $matches[2];
    $isCloseTag = $matches[1] == '/';
    $selfClosing = $matches[$num - 1] == '/';
    if (isset($this->allow[$tag])) {
      return '<' . $tag . ($selfClosing ? '/' : '') . '>';
    }
    return '';
  }

  function strip($text, $allow = array()) {
    $this->allow = $allow;
    $text =
      preg_replace_callback('/<(\/?)(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)(\/?)>/', array($this, 'replaceTags'), $text);
    print_r($matches);
    return $text;
  }
}

function output($text) {
  echo '<div style="font-size:10px;margin:5px;width:500px;padding:4px;border:4px dashed #ccc;">';
  echo $text;
  echo '</div>';
}

$comment = <<<END
<a href="javascript:alert('xss');" onmouseover="alert('xss');">Naked ladies</a><br/>
  <br>
  <p>Hello, World</p>
</div>
  <div style=color:blue>this is green</div>
  <meta name="derp" value="lort"/>
END;

output($comment);

output(nl2br(strip_tags($comment)));

$allow = array(
  'div' => array(),
  'br' => array(),
  'a' => array('href'),
  'p' => array()
);

$format = new Format();

output($format->strip($comment, $allow));


