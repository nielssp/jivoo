<?php

interface IEditor {
  public function init(AppConfig $config = null);

  public function getFormat();

  public function field(FormHelper $Form, $field, $options = array());
}
