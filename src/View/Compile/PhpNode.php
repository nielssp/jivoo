<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * A PHP expression or statement.
 * @property-read string $code PHP code.
 * @property-read bool $statement True if statement, fasle if expression.
 */
class PhpNode extends TemplateNode {
  /**
   * @var string PHP Code.
   */
  private $code = '';
  
  /**
   * @var bool True if statement.
   */
  private $statement = false;

  /**
   * Construct PHP expression or statement.
   * @param string $code PHP code.
   * @param bool $statement True if statement, false if expression. 
   */
  public function __construct($code, $statement = false) {
    parent::__construct();
    if (!$statement)
      $code = rtrim(trim($code), ';');
    $this->code = $code;
    $this->statement = $statement;
  }
  
  /**
   * Create a PHP literal from a value.
   * @param mixed $value Value.
   * @return PhpNode PHP expression.
   */
  public static function export($value) {
    return new PhpNode(var_export($value, true));
  }
  
  /**
   * Create a PHP expression from a node.
   * @param TemplateNode $node Node.
   * @return PhpNode PHP expression.
   */
  public static function expr(TemplateNode $node) {
    if ($node instanceof PhpNode) {
      if (!$node->statement)
        return $node;
      return self::export(null);
    }
    if ($node instanceof TextNode)
      return self::export($node->text);
    return self::export($node->__toString());
  }
  
  /**
   * Create a PHP array-expression from an HTML node's attributes.
   * @param HtmlNode $node HTML node.
   * @return PhpNode PHP array-expression.
   */
  public static function attributes(HtmlNode $node) {
    $attributes = array();
    foreach ($node->attributes as $key => $value)
      $attributes[] = var_export($key, true) . ' => ' . self::expr($value)->code;
    return new PhpNode('array(' . implode(',', $attributes) . ')');
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'code':
      case 'statement':
        return $this->$property;
    }
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    if ($this->statement) {
      $code = trim($this->code);
      $last = substr($code, -1);
      $semi = '';
      if ($last != ';' and $last != ':' and $last != '}')
        $semi = ';';
      return '<?php ' . $code . $semi . ' ?>';
    }
    else {
      return '<?php echo ' . $this->code . '; ?>';
    }
  }
}
