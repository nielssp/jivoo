<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class TextNode extends TemplateNode {
  private $text;

  public function __construct($text) {
    parent::__construct();
    $this->text = $text;
  }

  public function __get($property) {
    switch ($property) {
      case 'text':
        return $this->$property;
    }
    return parent::__get($property);
  }

  public function __toString() {
    $text = $this->text;
    if (trim($text) == '')
      return "\n";
    if ($text[0] == ' ')
      $text = "\n" . ltrim($text);
    if (substr($text, -1) == ' ')
      $text = rtrim($text) . "\n";
    return $text;
  }
}