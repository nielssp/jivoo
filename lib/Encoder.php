<?php

class Encoder {
  // source: http://xahlee.info/js/html5_non-closing_tag.html
  private $selfClosingTags = array(
    'area' => true,
    'base' => true,
    'br' => true,
    'col' => true,
    'command' => true,
    'embed' => true,
    'hr' => true,
    'img' => true,
    'input' => true,
    'keygen' => true,
    'link' => true,
    'meta' => true,
    'param' => true,
    'source' => true,
    'track' => true,
    'wbr' => true
  );
  
  private $allowAll = false;
  private $stripAll = false;
  
  private $allow = array();

  private $append = array();

  private $openTags = array();

  private $maxLength = -1;

  /** XHTML/HTML5  (not valid to use <br /> in HTML4) */
  private $xhtml = true;

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

  public function setXhtml($xhtml = true) {
    $this->xhtml = $xhtml;
  }
  
  public function setAllowAll($allowAll = false) {
    $this->allowAll = $allowAll;
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

  public function appendAttributes($tag, $attributes) {
    if (!isset($this->append[$tag])) {
      $this->append[$tag] = '';
    }
    if ($attributes[0] != ' ') {
      $attributes = ' ' . $attributes;
    }
    $this->append[$tag] .= $attributes;
  }

  public function allowAttribute($tag, $attribute) {
    $this->allowTag($tag);
    if (!isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute] = array();
    }
  }

  public function validateAttribute($tag, $attribute, $type, $stripTag = false) {
    if (isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute][$type] = $stripTag;
    }
  }

  private function isValid($value, $rule) {
    switch ($rule) {
      case 'url':
        return preg_match('/^("|\')?https?:\/\//i', $value) == 1;
    }
    return true;
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
        $valid = true;
        foreach ($rules as $rule => $stripTag) {
          if (!$this->isValid($value, $rule)) {
            if ($stripTag) {
              return false;
            }
            else {
              $valid = false;
            }
          }
        }
        if ($valid) {
          $newString .= ' ' . $attribute . '=' . $value;
        }
      }
      else if ($this->allowAll) {
        $newString .= ' ' . $attribute . '=' . $values[$key];
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
      $selfClosing = true;
    }
    if ((isset($this->allow[$tag]) OR $this->allowAll) AND !$this->stripAll) {
      if ($isCloseTag AND (!isset($this->openTags[$tag])
          OR $this->openTags[$tag] < 1)) {
        return '';
      }
      $attributes = $this->replaceAttributes($tag, $attributes);
      if ($attributes !== false ) {
        $clean = '<' . ($isCloseTag ? '/' : '');
        $clean .= $tag;
        if (!$isCloseTag) {
          $clean .= $attributes;
          if (isset($this->append[$tag])) {
            $clean .= $this->append[$tag];
          }
        }
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

  public function encode($text, $options = array()) {
    $this->openTags = array();
    if (isset($options['maxLength'])) {
      $maxLength = $options['maxLength'];
    }
    else {
      $maxLength = $this->maxLength;
    }
    if (isset($options['stripAll']) AND $options['stripAll'] == true) {
      $this->stripAll = true;
    }
    $shortened = false;
    if ($maxLength > 0 AND strlen($text) > $maxLength) {
      $text = substr($text, 0, $maxLength);
      $shortened = true;
    }
    $text =
      preg_replace_callback('/<((\/?)(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)(\/?)>)?/', array($this, 'replaceTag'), $text);
    foreach ($this->openTags as $tag => $number) {
      for ($i = 0; $i < $number; $i++) {
        $text .= '</' . $tag . '>';
      }
    }
    $this->stripAll = false;
    if ($shortened AND isset($options['append'])) {
      $text .= $options['append'];
    }
    return $text;
  }
}
