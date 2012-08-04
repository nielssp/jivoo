<?php

interface IEditor {
  public function getFormat();

  public function field(FormHelper $Form, $field, $options = array());
}
