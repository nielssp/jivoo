<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A foreach loop node.
 */
class ForeachNode extends InternalNode {
  /**
   * @var string Expression.
   */
  private $foreach;

  /**
   * Construct foreach loop. 
   * @param string $foreach Expression.
   */
  public function __construct($foreach) {
    parent::__construct();
    $this->foreach = $foreach;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $code = '<?php foreach (' . $this->foreach . '): ?>' . "\n";
    $code .= parent::__toString();
    $code .= '<?php endforeach; ?>' . "\n";
    return $code;
  }
}