<?php
class TimestampsMixin extends ActiveModelMixin {
  public function beforeSave(ActiveRecord $record) {
    if (!$record->isNew())
      $record->updatedAt = time();
  }
  
  public function afterCreate(ActiveRecord $record) {
    $record->createdAt = time();
    $record->updatedAt = time();
  }
}
