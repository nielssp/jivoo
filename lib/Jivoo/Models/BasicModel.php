<?php
/**
 * A basic model implementation.
 */
class BasicModel implements IBasicModel {
  
  /**
   * @var string[] Associative array of fields and labels.
   */
  private $labels = array();
  
  /**
   * @var DataType[] Associative array of fields and types.
   */
  private $types = array();
  
  /**
   * @var bool[] Associative array of fields and whether or not they are
   * required.
   */
  private $required = array();
  
  /**
   * @var string Model name.
   */
  private $name;
  
  /**
   * Construct basic model.
   * @param string $name Model name.
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Add field to model.
   * @param string $field Field name.
   * @param string $label Field label.
   * @param DataType $type Field type.
   */
  protected function addField($field, $label, DataType $type) {
    $this->labels[$field] = $label;
    $this->types[$field] = $type;
    if (!$type->null)
      $this->required[$field] = true;
  }
  
  /**
   * {@inherit}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inherit}
   */
  public function getFields() {
    return array_keys($this->labels);
  }

  /**
   * {@inherit}
   */
  public function getLabel($field) {
    if (isset($this->labels[$field]))
      return $this->labels[$field];
    return null;
  }

  /**
   * {@inherit}
   */
  public function getType($field) {
    if (isset($this->types[$field]))
      return $this->types[$field];
    return null;
  }

  /**
   * {@inherit}
   */
  public function hasField($field) {
    return isset($this->labels[$field]);
  }

  /**
   * {@inherit}
   */
  public function isRequired($field) {
    return isset($this->required[$field]);
  }
}