<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console\Generators;

use Jivoo\Console\GeneratorSnippet;
use Jivoo\Models\DataType;

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
    $this->viewData['types'] = array(
      DataType::INTEGER => tr('Integer'),
      DataType::STRING => tr('String'),
      DataType::TEXT => tr('Text'),
      DataType::BOOLEAN => tr('Bool'),
      DataType::FLOAT => tr('Float'),
      DataType::DATE => tr('Date'),
      DataType::DATETIME => tr('Date/time'),
      DataType::BINARY => tr('Binary'),
      DataType::OBJECT => tr('Object'),
      DataType::ENUM => tr('Enum')
    );
    return $this->render();
  }
}