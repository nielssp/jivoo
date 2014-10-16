<?php
interface IFormExtension extends IViewExtension {
  public function label($label = null, $attributes = array());
  public function ifRequired($output);
  public function field($attributes = array());
  public function error($default = '');
}