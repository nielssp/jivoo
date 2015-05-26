<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class IfNode extends TemplateNode {
  private $condition = '';
  private $then;
  private $else;

  public function __construct($condition, TemplateNode $then = null) {
    parent::__construct();
    $this->condition = $condition;
    $this->then = new InternalNode();
    $this->else = new InternalNode();
    if (isset($then))
      $this->then->append($then);
  }

  public function __get($property) {
    switch ($property) {
      case 'then':
      case 'else':
        return $this->$property;
    }
    return parent::__get($property);
  }

  public function __toString() {
    $code = '<?php if (' . $this->condition . '): ?>' . "\n";
    $code .= $this->then->__toString();
    if (count($this->else) > 0) {
      $code .= '<?php else: ?>' . "\n";
      $code .= $this->else->__toString();
    }
    $code .= '<?php endif; ?>' . "\n";
    return $code;
  }
}