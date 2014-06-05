<?php
class EnumDataType extends DataType {
  
  private $values;
  
  protected function __construct($enumClass, $null = false, $default = null) {
    parent::__construct(DataType::ENUM, $null, $default);
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