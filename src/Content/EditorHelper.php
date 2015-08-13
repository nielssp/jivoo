<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\Helper;
use Jivoo\ActiveModels\ActiveModel;

/**
 * Helper for editors.
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
   * @param Editor $editor Editor object.
   */
  public function set(ActiveModel $model, $field, Editor $editor) {
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
      $format = $this->Format->formatOf($record, $field);
      if (!isset($format)) {
        $formatField = $field . 'Format';
        return 'Error: Unknown format: ' . $record->$formatField;
      } 
      return 'Error: No editor available for format: ' . $format->getName();
    }
    return $editor->field($this->Form, $field, $options);
  }
}
