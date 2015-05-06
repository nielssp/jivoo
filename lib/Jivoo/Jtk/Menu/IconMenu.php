<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Form;

use Jivoo\Jtk\JtkSnippet;

/**
 * A recursive icon menu snippet.
 */
class IconMenu extends JtkSnippet {
  protected $helpers = array('Icon');
  
  protected $viewData = array(
    'menu' => null,
  );
  
  protected $autoSetters = array('menu');
}

