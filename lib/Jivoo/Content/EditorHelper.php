<?php
/**
 * Helper for editors.
 * @package Jivoo\Content
 */
class EditorHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Content');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form', 'Format');

  /**
   * Set editor for model field.
   * @param ActiveModel $model A model.
   * @param string $field Field name.
   * @param IEditor $editor Editor object.
   */
  public function set(ActiveModel $model, $field, IEditor $editor) {
    $this->m->Content->setEditor($model, $field, $editor);
  }

  /**
   * Get editor for field (must be in a form-context using {@see FormHelper}).
   * @param string $field Field name.
   * @param array $options Associative array of options for editor.
   * @return string Editor field HTML.
   */
  public function get($field, $options = array()) {
    $record = $this->Form->getRecord();
    $editor = $this->m->Content->getEditor($record, $field);
    if (!isset($editor)) {
      // TODO convert / change editor etc...
      return 'Error: No editor available for format: ' . $this->Format->formatOf($record, $field)->getName();
    }
    return $editor->field($this->Form, $field, $options);
  }
}
