<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console\Generators;

use Jivoo\Console\GeneratorSnippet;

/**
 * Schema generator.
 */
class SchemaGenerator extends GeneratorSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');
  
  /**
   * {@inheritdoc}
   */
  public function post($data) {
    return $this->get();
  }
  
  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->viewData['title'] = tr('Schema generator');
    return $this->render();
  }
}