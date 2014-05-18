<?php

abstract class ActiveModelMixin extends Module implements IEventListener {
  /** @var ActiveModel Model */
  protected $model;
  
  protected $options = array();
  
  public final function __construct(App $app, ActiveModel $model, $options = array()) {
    parent::__construct($app);
    $this->model = $model;
    $this->options = array_merge($this->options, $options);
    $this->init();
  }
  
  public function getEventHandlers() {
    return array('beforeSave', 'afterSave', 'beforeValidate', 'afterValidate', 'afterCreate', 'afterLoad', 'beforeDelete', 'install');
  }

  public function init() { }

  public function beforeSave(ActiveModelEvent $event) { }
  public function afterSave(ActiveModelEvent $event) { }
  
  public function beforeValidate(ActiveModelEvent $event) { }
  public function afterValidate(ActiveModelEvent $event) { }
  
  public function afterCreate(ActiveModelEvent $event) { }
  
  public function afterLoad(ActiveModelEvent $event) { }
  
  public function beforeDelete(ActiveModelEvent $event) { }

  public function install(ActiveModelEvent $event) { }
}
