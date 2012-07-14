<?php

class Encoder {
  // source: http://xahlee.info/js/html5_non-closing_tag.html
  private $selfClosingTags = array(
    'area' => TRUE,
    'base' => TRUE,
    'br' => TRUE,
    'col' => TRUE,
    'command' => TRUE,
    'embed' => TRUE,
    'hr' => TRUE,
    'img' => TRUE,
    'input' => TRUE,
    'keygen' => TRUE,
    'link' => TRUE,
    'meta' => TRUE,
    'param' => TRUE,
    'source' => TRUE,
    'track' => TRUE,
    'wbr' => TRUE
  );
  private $allow = array();

  private $openTags = array();

  private $maxLength = -1;

  /** XHTML/HTML5  (not valid to use <br /> in HTML4) */
  private $xhtml = TRUE;

  public function __construct(Configuration $config = NULl) {
    if (isset($config)) {
      if (isset($config['allow'])) {
        $this->allow = $config['allow'];
      }
      if (isset($config['xhtml'])) {
        $this->xhtml = (bool)$config['xhtml'];
      }
    }
  }

  public function setXhtml($xhtml = TRUE) {
    $this->xhtml = $xhtml;
  }

  public function setAllowed($allow = array()) {
    $this->allow = $allow;
  }

  public function setMaxLength($length = -1) {
    $this->maxLength = $length;
  }

  public function allowTag($tag) {
    if (!isset($this->allow[$tag])) {
      $this->allow[$tag] = array('attributes' => array());
    }
  }

  public function allowAttribute($tag, $attribute) {
    $this->allowTag($tag);
    if (!isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute] = array();
    }
  }

  public function validateAttribute($tag, $attribute, $type, $stripTag = FALSE) {
    if (isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute][$type] = $stripTag;
    }
  }

  private function isValid($value, $rule) {
    switch ($rule) {
      case 'url':
        return preg_match('/^("|\')?https?:\/\//i', $value) == 1;
    }
    return TRUE;
  }

  private function replaceAttributes($tag, $attributes) {
    preg_match_all('/\s+(\w+)(\s*=\s*((?:".*?"|\'.*?\'|[^\'">\s]+)))?/', $attributes, $matches);
    $attributes = $matches[1];
    $values = $matches[3];
    $num = count($attributes);
    $newString = '';
    foreach ($attributes as $key => $attribute) {
      $attribute = strtolower($attribute);
      if (isset($this->allow[$tag]['attributes'][$attribute])) {
        $rules = $this->allow[$tag]['attributes'][$attribute];
        $value = $values[$key];
        $valid = TRUE;
        foreach ($rules as $rule => $stripTag) {
          if (!$this->isValid($value, $rule)) {
            if ($stripTag) {
              return FALSE;
            }
            else {
              $valid = FALSE;
            }
          }
        }
        if ($valid) {
          $newString .= ' ' . $attribute . '=' . $value;
        }
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
    if (isset($this->selfClosingTags[$tag])) {
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
        if ($this->xhtml) {
          $clean .= ($selfClosing ? ' /' : '') . '>';
        }
        else {
          $clean .= '>';
        }

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

  public function encode($text) {
    $this->openTags = array();
    if ($this->maxLength > 0) {
      $text = substr($text, 0, $this->maxLength);
    }
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
