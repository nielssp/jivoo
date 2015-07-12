<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

/**
 * Mixin for adding meta data stored in a separate model to records.
 *
 * The settings are:
 * <code>
 * array(
 *   'model' => // the meta data model, e.g. 'UserMeta'
 *   'thisKey' => // the foreign key in the above model, e.g. 'userId'
 *   'otherKey' => // the primary key of the current model, e.g. 'id'
 * )
 * </code>
 */
class MetaMixin extends ActiveModelMixin {
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'model' => null,
    'recordKey' => null
  );

  /**
   * @var IModel Meta data model.
   */
  private $other;

  /**
   * {@inheritdoc}
   */
  public function init() {
    if (!isset($this->options['model']))
      $this->options['model'] = $this->model->getName() . 'Meta';
    if (!isset($this->options['recordKey']))
      $this->options['thisKey'] = lcfirst($this->model->getName()) . 'Id';
    $this->model->addVirtual('meta');
    $other = $this->options['model'];
    $db = $this->model->getDatabase();
    if (!isset($db->$other)) {
      throw new ModelNotFoundException(tr(
        'Model %1 not found in %2', $other, $this->model->getName()
      ));
    }
    $this->other = $db->$other;
  }

  /**
   * {@inheritdoc}
   */
  public function afterLoad(ActiveModelEvent $event) {
    $recordKey = $this->options['recordKey'];
    $event->record->meta = new Meta($this->other, $thisKey, $event->record);
  }

  /**
   * {@inheritdoc}
   */
  public function afterCreate(ActiveModelEvent $event) {
    $recordKey = $this->options['recordKey'];
    $event->record->meta = new Meta($this->other, $thisKey, $event->record);
  }

  /**
   * {@inheritdoc}
   */
  public function afterSave(ActiveModelEvent $event) {
    $event->record->meta->save();
  }
}
