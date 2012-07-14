<?php

interface IEditor {

  public function init();

  public function setEncoder(Encoder $encoder);
  
  public function getFormat();

  public function field($name, $id, $value = NULL, $options = array());
}
