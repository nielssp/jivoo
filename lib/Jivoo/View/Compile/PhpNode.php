<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

class PhpNode extends TemplateNode {
  private $code = '';
  private $statement = false;

  public function __construct($code, $statement = false) {
    parent::__construct();
    if (!$statement)
      $code = rtrim(trim($code), ';');
    $this->code = $code;
    $this->statement = $statement;
  }

  public function __get($property) {
    switch ($property) {
      case 'code':
      case 'statement':
        return $this->$property;
    }
    return parent::__get($property);
  }

  public function __toString() {
    if ($this->statement) {
      $code = trim($this->code);
      $last = substr($code, -1);
      $semi = '';
      if ($last != ';' and $last != ':')
        $semi = ';';
      return '<?php ' . $this->code . $semi . ' ?>' . "\n";
    }
    else {
      return '<?php echo ' . $this->code . '; ?>';
    }
  }
}
