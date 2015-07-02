<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\IBasicRecord;

interface IFormExtension {
  public function getName();
  public function getValue(IBasicRecord $record = null);
  public function getLabel();
  public function getField($attributes = array());
  public function isRequired();
  public function getError();
}