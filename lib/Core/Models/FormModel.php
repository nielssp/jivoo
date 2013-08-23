<?php
/**
 * Model for generic forms
 * @package Core\Models
 * @property-read array $fields Associative array of field names and
 * information about the field in an associative array structure ('label', 
 * 'type' and 'required')
 */
class FormModel implements IModel {
  /**
   * @var string Form name
   */
  private $name;
  
  /**
   * @var Associative array of field names and information (label, type and
   * required)
   */
  private $fields = array();

  /**
   * Constructor.
   * @param string $name Form name
   */
  public function __construct($name) {
    $this->name = $name;
  }
  
  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'fields':
        return $this->$property;
    }
  }

  /**
   * Add a field to form
   * @param string $field Field name
   * @param string $type Type of field, e.g. 'string', 'text', 'date', 'hidden'
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addField($field, $type = 'string', $label = null,
                           $required = true) {
    if (!isset($label)) {
      $label = tr(ucfirst($field));
    }
    $this->fields[$field] = array('label' => $label, 'type' => $type,
      'required' => $required
    );
  }

  /**
   * Add a string field to form
   * @param string $field Field name
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addString($field, $label = null, $required = true) {
    $this->addField($field, 'string', $label, $required);
  }

  /**
   * Add a text field to form
   * @param string $field Field name
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addText($field, $label = null, $required = true) {
    $this->addField($field, 'text', $label, $required);
  }
  
  /**
   * Add an error
   * @param string $field Field name
   * @param string $errorMsg Error message
   */
  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }

  /* IModel implementation */

  /**
   * @return Form New form from this model
   */
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
