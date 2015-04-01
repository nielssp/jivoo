<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration;

class PublishWidget extends Widget {
  protected $helpers = array('Form');
  
  protected $options = array(
    'record' => null,
    'title' => 'title',
    'content' => 'content',
    'route' => array()
  );
  
  public function main($options) {
    assume($options['record'] instanceof IBasicRecord);
    return $this->fetch();
  }
}