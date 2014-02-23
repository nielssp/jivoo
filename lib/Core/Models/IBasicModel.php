<?php
interface IBasicModel {

  public function getName();
  /**
   * @return IValidator
  */
  public function getValidator();

  /** @return string[] List of field names */
  public function getFields();

  /**
   * Get type of field
   * @param string $field Field name
   * @return DataType|null Type of field if it exists
   */
  public function getType($field);

  /**
   * Get editor
   * @param string $field Field name
   * @return IEditor|null An editor if it exists
   */
  public function getEditor($field);

  public function getLabel($field);

  public function hasField($field);

  public function isRequired($field);
}
