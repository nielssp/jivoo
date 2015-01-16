<?php
/**
 * Mixin for automatically updating time stamps on records when they are 
 * created and updated. Assumes existence of DateTime-fields "updated"
 * and "created".
 * @package Jivoo\ActiveModels
 */
class TimestampsMixin extends ActiveModelMixin {
  /**
   * {@inheritdoc}
   */
  public function beforeValidate(ActiveModelEvent $event) {
    if (!$event->record->isNew())
      $event->record->updated = time();
  }

  /**
   * {@inheritdoc}
   */
  public function afterCreate(ActiveModelEvent $event) {
    $event->record->created = time();
    $event->record->updated = time();
  }
}
