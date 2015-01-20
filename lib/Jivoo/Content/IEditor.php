<?php
/**
 * An editor.
 * @package Jivoo\Content
 */
interface IEditor {
  /**
   * Get content format used by editor.
   * @return IContentFormat Format object.
   */
  public function getFormat();

  /**
   * Get HTML code for this editor.
   * @param FormHelper $Form A form helper.
   * @param string $field Name of field.
   * @param array $options Additional options.
   * @return string HTML.
   */
  public function field(FormHelper $Form, $field, $options = array());
}
