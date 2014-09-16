<?php
class EnumDataType extends DataType {
  
  private $class;
  private $values;
  
  protected function __construct($enumClass, $null = false, $default = null) {
    parent::__construct(DataType::ENUM, $null, $default);
    $this->class = $enumClass;
    $this->values = Enum::getValues($enumClass);
    if (isset($default) and !in_array($default, $this->values)) {
      throw new InvalidArgumentException(tr(
        'Default value must be part of enum'
      ));
    }
  }

  public function __get($property) {
    if ($property == 'values')
      return $this->values;
    if ($property == 'placeholder')
      return '%' . $this->class;
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