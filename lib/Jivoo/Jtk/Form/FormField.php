<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Form;

use Jivoo\Jtk\JtkSnippet;

/**
 * A single form field.
 */
class FormField extends JtkSnippet {
  protected $helpers = array('Form');
  
  protected $viewData = array(
    'field' => null,
    'label' => null,
    'labelAttributes' => array(),
  );
  
  protected $autoSetters = array('field');
  
  public function label($label, $attributes = array()) {
    $this->viewData['label'] = $label;
    $this->viewData['labelAttributes'] = $attributes;
    return $this;
  }
}

