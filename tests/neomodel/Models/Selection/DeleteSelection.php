<?php
class DeleteSelection extends BasicSelection implements IDeleteSelection {
  public function delete() {
    $this->model->delete($this);
  }
}