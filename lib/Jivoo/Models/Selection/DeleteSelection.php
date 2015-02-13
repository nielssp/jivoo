<?php
/**
 * A delete selection.
 */
class DeleteSelection extends BasicSelection implements IDeleteSelection {
  /**
   * {@inheritdoc}
   */
  public function delete() {
    $this->model->deleteSelection($this);
  }
}