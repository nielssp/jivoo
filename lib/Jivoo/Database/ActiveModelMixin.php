<?php

abstract class ActiveModelMixin implements IActiveModelEvents {
  /** @var ActiveModel Model */
  protected $model;
  
  protected $options = array();
  
  public final function __construct(ActiveModel $model, $options = array()) {
    $this->model = $model;
    $this->options = array_merge($this->options, $options);
    $this->init();
  }

  public function init() { }

  public function beforeSave(ActiveRecord $record) { }
  public function afterSave(ActiveRecord $record) { }
  
  public function beforeValidate(ActiveRecord $record) { }
  public function afterValidate(ActiveRecord $record) { }
  
  public function afterCreate(ActiveRecord $record) { }
  
  public function afterLoad(ActiveRecord $record) { }
  
  public function beforeDelete(ActiveRecord $record) { }

  public function install() { }
}
