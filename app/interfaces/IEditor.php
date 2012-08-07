<?php

interface IEditor {
  public function init(Configuration $config = NULL);

  public function getFormat();

  public function field(FormHelper $Form, $field, $options = array());
}
