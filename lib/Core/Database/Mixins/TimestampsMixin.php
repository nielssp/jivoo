<?php
class TimestampsMixin extends ActiveModelMixin {
  public function beforeValidate(ActiveRecord $record) {
    $record->updatedAt = time();
  }
  
  public function afterCreate(ActiveRecord $record) {
    $record->createdAt = time();
  }
}
