<?php
class TimestampsMixin extends ActiveModelMixin {
  public function beforeValidate(ActiveModelEvent $event) {
    if (!$event->record->isNew())
      $event->record->updated = time();
  }
  
  public function afterCreate(ActiveModelEvent $event) {
    $event->record->created = time();
    $event->record->updated = time();
  }
}
