<?php

abstract class ActiveModelMixin {
  /** @var ActiveModel Model */
  protected $model;
  
  protected $options = array();
  
  public final function __construct(ActiveModel $model, $options = array()) {
    $this->model = $model;
    $this->options = array_merge($this->options, $options);
  }
}