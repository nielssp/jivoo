<?php
class EnumDataType extends DataType {
  
  private $class = null;
  private $values;
  
  protected function __construct($valuesOrClass, $null = false, $default = null) {
    parent::__construct(DataType::ENUM, $null, $default);
    if (is_array($valuesOrClass)) {
      $this->values = $valuesOrClass;
    }
    else { 
      $this->class = $valuesOrClass;
      $this->values = Enum::getValues($this->class);
    }
    if (isset($default) and !in_array($default, $this->values)) {
      throw new InvalidArgumentException(tr(
        'Default value must be part of enum'
      ));
    }
  }

  public function __get($property) {
    if ($property === 'values')
      return $this->values;
    if ($property === 'placeholder') {
      if (!isset($this->class))
        throw new Exception(tr('Invalid use of anonymous enum type'));
      return '%' . $this->class;
    }
    return parent::__get($property);
  }

  public function createValidationRules(ValidatorField $validator) {
    $validator = $validator->ruleDataType;
    if (!$this->null)
      $validator->null = false;
    $validator->in = array_values($this->values);
  }
  
  public function isValid($value) {
    if ($this->null and $value == null)
      return true;
    return in_array($value, $this->values);
  }
}
