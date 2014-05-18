<?php
class TimestampsMixin extends ActiveModelMixin {
  public function beforeSave(ActiveModelEvent $event) {
    if (!$event->record->isNew())
      $event->record->updatedAt = time();
  }
  
  public function afterCreate(ActiveModelEvent $event) {
    $event->record->createdAt = time();
    $event->record->updatedAt = time();
  }
}
