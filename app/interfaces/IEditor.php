<?php

interface IEditor {

  public function init();

  public function setEncoder(Encoder $encoder);
  
  public function getFormat();

  public function field(FormHelper $Form, $field, $options = array());
}
