<?php

class Format {
  private $allow = array();

  private $openTags = array();

  private function replaceAttributes($tag, $attributes) {
    preg_match_all('/\s+(\w+)(\s*=\s*((?:".*?"|\'.*?\'|[^\'">\s]+)))?/', $attributes, $matches);
    $attributes = $matches[1];
    $values = $matches[3];
    $num = count($attributes);
    $newString = '';
    foreach ($attributes as $key => $attribute) {
      if (isset($this->allow[$tag][$attribute])) {
        $value = $values[$key];
        if (isset($this->allow[$tag][$attribute]['url'])) {
          if (preg_match('/^("|\')?https?:\/\//i', $value) == 0) {
            return FALSE;
          }
        }
        $newString .= ' ' . $attribute . '=' . $value;
      }
    }
    return $newString;
  }

  private function openTag($tag) {
    if (!isset($this->openTags[$tag])) {
      $this->openTags[$tag] = 0;
    }
    $this->openTags[$tag]++;
  }

  private function closeTag($tag) {
    if (!isset($this->openTags[$tag])) {
      $this->openTags[$tag] = 0;
    }
    $this->openTags[$tag]--;
  }

  private function replaceTag($matches) {
    $num = count($matches);
    $tag = $matches[2];
    $attributes = $matches[3];
    $isCloseTag = $matches[1] == '/';
    $selfClosing = $matches[$num - 1] == '/';
    if ($tag == 'br') {
      $selfClosing = TRUE;
    }
    if (isset($this->allow[$tag])) {
      if ($isCloseTag AND (!isset($this->openTags[$tag])
          OR $this->openTags[$tag] < 1)) {
        return '';
      }
      $attributes = $this->replaceAttributes($tag, $attributes);
      if ($attributes !== FALSE ) {
        $clean = '<' . ($isCloseTag ? '/' : '');
        $clean .= $tag;
        $clean .= $attributes;
        $clean .= ($selfClosing ? ' /' : '') . '>';
        if ($isCloseTag) {
          $this->closeTag($tag);
        }
        else if (!$selfClosing) {
          $this->openTag($tag);
        }
        return $clean;
      }
    }
    return '';
  }

  public function strip($text, $allow = array()) {
    $this->allow = $allow;
    $text =
      preg_replace_callback('/<(\/?)(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)(\/?)>/', array($this, 'replaceTag'), $text);
    foreach ($this->openTags as $tag => $number) {
      for ($i = 0; $i < $number; $i++) {
        $text .= '</' . $tag . '>';
      }
    }
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
  </div><div>
  <div style=color:blue>this is green</div>
  <meta name="derp" value="lort"/>
  <a href="http://apakoh.dk">Hello</a>
END;

output($comment);

output(nl2br(strip_tags($comment)));

$allow = array(
  'div' => array(),
  'br' => array(),
  'a' => array(
    'href' => array('url' => TRUE)
  ),
  'p' => array()
);

$format = new Format();

output($format->strip($comment, $allow));


