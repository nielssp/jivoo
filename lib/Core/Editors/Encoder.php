<?php
/**
 * Encoder for encoding HTML, allowing/disallowing certain tags and attributes,
 * and automatically closing tags and stripping invalid HTML.
 * @package Core\Editors
 */
class Encoder {
  /**
   * HTML5 tags that should not be closed.
   * 
   * Source: http://xahlee.info/js/html5_non-closing_tag.html
   * @var array Associative array of lowercase tag-names and true-values.
   */
  private $selfClosingTags = array('area' => true, 'base' => true,
    'br' => true, 'col' => true, 'command' => true, 'embed' => true,
    'hr' => true, 'img' => true, 'input' => true, 'keygen' => true,
    'link' => true, 'meta' => true, 'param' => true, 'source' => true,
    'track' => true, 'wbr' => true
  );

  /**
   * @var bool Whether or not to allow all tags
   */
  private $allowAll = false;
  
  /**
   * @var bool Whether r not to strip all tags
   */
  private $stripAll = false;

  /**
   * @var array Associative array of allowed tags and additional options
   */
  private $allow = array();

  /**
   * @var array Associative array of tags and lists of attributes that should
   * be appended to that tag.
   */
  private $append = array();

  /**
   * @var array Associative array of tags and number of open tags
   */
  private $openTags = array();

  /**
   * @var int Max length of encoded text (-1 means no limit)
   */
  private $maxLength = -1;

  /** 
   * @var bool Whether or not to encode XHTML rather than HTML
   * (it is not valid to use <br /> in HTML4)
   * */
  private $xhtml = true;

  /**
   * Constructor
   * @param AppConfig $config Encoder configuration, accepted configuration keys
   * are 'allow' and 'xhtml'. 'xhtml' is expected to be a boolean, see 
   * {@see Encoder::setXhtml()}. 'allow' is expected to be an associative array of
   * allowed tags and additional options, see {@see Encoder::setAllowed()} for
   * array format.
   */
  public function __construct(AppConfig $config = NULl) {
    if (isset($config)) {
      if (isset($config['allow'])) {
        $this->allow = $config['allow'];
      }
      if (isset($config['xhtml'])) {
        $this->xhtml = (bool) $config['xhtml'];
      }
    }
  }

  /**
   * Set whether or not to encode for XHTML (<code><br></code> versus
   * <code><br /></code> (which is not allowed in HTML4)).
   * @param bool $xhtml Whether or not to encode for XHTML  
   */
  public function setXhtml($xhtml = true) {
    $this->xhtml = $xhtml;
  }

  /**
   * Allow all tags
   * @param bool $allowAll Whether or not to allow all tags 
   */
  public function setAllowAll($allowAll = false) {
    $this->allowAll = $allowAll;
  }

  /**
   * Set allowed tags.
   * 
   * The format of the $allow array is:
   * <code>
   * array(
   *   'strong' => array(          // Allow the <strong> tag
   *     'attributes' => array()   // Allow no attributes on <strong> tag
   *   ),
   *   'a' => array(               // Allow the <a> tag
   *     'attributes' => array(    
   *       'href' => array(        // Allow the href="" attribute
   *         'url' => true         // Validate value of attribute as URL
   *       )                       // and strip entire tag if invalid,
   *     )                         // when set to false it will only remove
   *   )                           // attribute
   * )
   * </code>
   * Currently the only supported attribute validation is 'url'.
   * @param array $allow Allowed tags
   */
  public function setAllowed($allow = array()) {
    $this->allow = $allow;
  }

  /**
   * Set max length of encoded text, additional text will be removed and
   * tags unclosed tags automatically closed
   * @param int $length Max length or -1 for no limit
   */
  public function setMaxLength($length = -1) {
    $this->maxLength = $length;
  }

  /**
   * Allow an HTML tag
   * @param string $tag Name of tag, e.g. 'h1' or 'strong'
   */
  public function allowTag($tag) {
    if (!isset($this->allow[$tag])) {
      $this->allow[$tag] = array('attributes' => array());
    }
  }

  /**
   * Automatically append attributes to all occurences of a tag
   * @param string $tag Name of tag, e.g. 'a'
   * @param string $attributes Attribute(s) to append, e.g. 'rel="no-follow"'
   */
  public function appendAttributes($tag, $attributes) {
    if (!isset($this->append[$tag])) {
      $this->append[$tag] = '';
    }
    if ($attributes[0] != ' ') {
      $attributes = ' ' . $attributes;
    }
    $this->append[$tag] .= $attributes;
  }

  /**
   * Allow an attribute on a tag
   * @param string $tag Name of tag, e.g. 'a'
   * @param string $attribute Name of attribute, e.g. 'href'
   */
  public function allowAttribute($tag, $attribute) {
    $this->allowTag($tag);
    if (!isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute] = array();
    }
  }

  /**
   * Validate value of all occurences of a tag attribute
   * 
   * The $stripTags parameter determines what to do with an invalid attribute:
   * 
   * With $stripTags set to false:
   * <code>
   * <a href="not-valid-link">Test</a> --> <a>Test</a>
   * </code>
   * With $stripTags set to true:
   * <code>
   * <a href="not-valid-link">Test</a> --> Test
   * </code>
   * @param string $tag Name of tag, e.g. 'img'
   * @param string $attribute Name of attribute, e.g. 'src'
   * @param string $type Type of validation: 'url' is the only supported value
   * at the moment
   * @param string $stripTag Whether or not to strip the entire tag if the value
   * is not valid. Default is to just remove the attribute.
   */
  public function validateAttribute($tag, $attribute, $type, $stripTag = false) {
    if (isset($this->allow[$tag]['attributes'][$attribute])) {
      $this->allow[$tag]['attributes'][$attribute][$type] = $stripTag;
    }
  }

  /**
   * Test if a value is valid according  to a rule
   * @param string $value Value to validate
   * @param string $rule Rule
   * @return boolean True if valid, false otherwise
   */
  private function isValid($value, $rule) {
    switch ($rule) {
      case 'url':
        return preg_match('/^("|\')?https?:\/\//i', $value) == 1;
    }
    return true;
  }

  /**
   * Replace attributes
   * @param string $tag Tag name
   * @param string $attributes The attributes-part of the tag
   * @return string|false Encoded string or false if entire tag should be removed
   */
  private function replaceAttributes($tag, $attributes) {
    preg_match_all('/\s+(\w+)(\s*=\s*((?:".*?"|\'.*?\'|[^\'">\s]+)))?/',
      $attributes, $matches);
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

  /**
   * Called when an open tag is detected
   * @param string $tag Tag name
   */
  private function openTag($tag) {
    if (!isset($this->openTags[$tag])) {
      $this->openTags[$tag] = 0;
    }
    $this->openTags[$tag]++;
  }

  /**
   * Called when a close tag is detected
   * @param string $tag Tag name
   */
  private function closeTag($tag) {
    if (!isset($this->openTags[$tag])) {
      $this->openTags[$tag] = 0;
    }
    $this->openTags[$tag]--;
  }

  /**
   * Working on a single HTML-object
   * @param string[] $matches Matches as produced by preg_replace_callback()
   * @return string Encoded string
   */
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
      if ($isCloseTag
          AND (!isset($this->openTags[$tag]) OR $this->openTags[$tag] < 1)) {
        return '';
      }
      $attributes = $this->replaceAttributes($tag, $attributes);
      if ($attributes !== false) {
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

  /**
   * Encode a text for display on an HTML page.
   * 
   * The options array accepts the following options:
   * * 'maxLength' Max length of encoded text, additional text will be removed.
   * * 'stripAll' Whether or not to strip all tags (bool)
   * * 'append' A string to append to the result if shortened by 'maxLength',
   *   e.g. '...'
   * @param string $text Text to encode
   * @param array $options Associative array of additional options
   * @return string The encoded text
   */
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
      $text = Utilities::substr($text, 0, $maxLength);
      $shortened = true;
    }
    $text = preg_replace_callback(
      '/<((\/?)(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)(\/?)>)?/',
      array($this, 'replaceTag'), $text);
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
