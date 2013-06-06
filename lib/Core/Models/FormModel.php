<?php
class FormModel implements IModel {

  private $name;
  private $fields = array();

  public function __construct($name) {
    $this->name = $name;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'fields':
        return $this->$property;
    }
  }

  public function addField($field, $type = 'string', $label = null,
                           $required = true) {
    if (!isset($label)) {
      $label = tr(ucfirst($field));
    }
    $this->fields[$field] = array('label' => $label, 'type' => $type,
      'required' => $required
    );
  }

  public function addString($field, $label = null, $required = true) {
    $this->addField($field, 'string', $label, $required);
  }

  public function addText($field, $label = null, $required = true) {
    $this->addField($field, 'text', $label, $required);
  }

  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }

  /* IModel implementation */

  public function create($data = array(), $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    return new Form($this, $data);
  }

  public function getName() {
    return $this->name;
  }

  public function getFields() {
    return array_keys($this->fields);
  }

  public function getFieldType($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['type'];
    }
  }

  public function getFieldLabel($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['label'];
    }
  }

  public function getFieldEditor($field) {
    return null;
  }

  public function isFieldRequired($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['required'];
    }
  }

  public function isField($field) {
    return isset($this->fields[$field]);
  }

  public function all(SelectQuery $query = null) {
    return array();
  }

  public function first(SelectQuery $query = null) {
    return null;
  }

  public function last(SelectQuery $query = null) {
    return null;
  }

  public function count(SelectQuery $query = null) {
    return 0;
  }
}
