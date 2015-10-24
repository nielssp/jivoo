<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

class DocumentVar {
  /**
   * @var Document
   */
  private $document;
  
  /**
   * @var string
   */
  private $var;
  
  public function __construct(Document $document, $var) {
    $this->document = $document;
    $this->var = $var;
  }
  
  public function get() {
    return $this->document->get($this->var);
  }
  
  public function setDefault($value) {
    $this->document->setDefault($this->var, $value);
  }
  
  public function exists() {
    return $this->document->exists($this->var);
  }
  
  public function set($value) {
    $this->document->set($this->var, $value);
  }
}