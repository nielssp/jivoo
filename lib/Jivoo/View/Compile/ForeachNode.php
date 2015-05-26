<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class ForeachNode extends InternalNode {
  private $foreach;

  public function __construct($foreach) {
    parent::__construct();
    $this->foreach = $foreach;
  }

  public function __toString() {
    $code = '<?php foreach (' . $this->foreach . '): ?>' . "\n";
    $code .= parent::__toString();
    $code .= '<?php endforeach; ?>' . "\n";
    return $code;
  }
}