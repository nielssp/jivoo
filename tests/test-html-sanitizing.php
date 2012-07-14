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
      $attribute = strtolower($attribute);
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
    if ($num < 8) {
      return htmlentities($matches[0]);
    }
    $tag = strtolower($matches[3]);
    $attributes = $matches[4];
    $isCloseTag = $matches[2] == '/';
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
      preg_replace_callback('/<((\/?)(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)(\/?)>)?/', array($this, 'replaceTag'), $text);
    foreach ($this->openTags as $tag => $number) {
      for ($i = 0; $i < $number; $i++) {
        $text .= '</' . $tag . '>';
      }
    }
    return $text;
  }
}

function output($text) {
  echo '<div style="font-size:10px;margin:5px;width:500px;padding:4px;border:4px dashed #ccc;">' . PHP_EOL . PHP_EOL;
  echo $text . PHP_EOL . PHP_EOL;
  echo '</div>' . PHP_EOL . PHP_EOL;
}

$comment = <<<END
<a href="javascript:alert('xss');" onmouseover="alert('xss');">Naked ladies</a><br/>
  <br>
  <P>Hello, World</p>
  </div><div>
  <div style=color:blue>this is green</div>
  <meta name="derp" value="lort"/>
  <a hReF="http://apakoh.dk" style="color:brown;">Hello</a>
END;

$comment2 = <<<END
<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>
<IMG """><SCRIPT>alert("XSS")</SCRIPT>">
<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>
<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>
<IMG SRC="jav ascript:alert('XSS');">
<IMG
SRC
=
"
j
a
v
a
s
c
r
i
p
t
:
a
l
e
r
t
(
  '
  X
  S
  S
  '
)
"
>

<IMG SRC=" &#14;  javascript:alert('XSS');">
<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>
<BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>
<<SCRIPT>alert("XSS");//<</SCRIPT>
<IMG SRC="javascript:alert('XSS')"
  <BR SIZE="&{alert('XSS')}">
  ¼script¾alert(¢XSS¢)¼/script¾
  <DIV STYLE="background-image: url(javascript:alert('XSS'))">
  <DIV STYLE="width: expression(alert('XSS'));">
END;

output($comment);

output(nl2br(strip_tags($comment)));

$allow = array(
  'div' => array(),
  'br' => array(),
  'a' => array(
    'href' => array('url' => TRUE)
  ),
  'p' => array(),
  'img' => array(
    'src' => array('url' => TRUE)
  ),
);

$format = new Format();

output($format->strip($comment, $allow));

output($format->strip($comment2, $allow));

