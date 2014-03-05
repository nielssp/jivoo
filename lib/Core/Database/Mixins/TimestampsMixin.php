<?php
class TimestampsMixin extends ActiveModelMixin {
  public function beforeSave(ActiveRecord $record) {
    $record->updatedAt = time();
  }
  
  public function afterCreate(ActiveRecord $record) {
    $record->createdAt = time();
  }
}