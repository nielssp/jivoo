<?php
/**
 * A delete selection.
 * @package Jivoo\Models\Selection
 */
class DeleteSelection extends BasicSelection implements IDeleteSelection {
  /**
   * {@inheritdoc}
   */
  public function delete() {
    $this->model->deleteSelection($this);
  }
}