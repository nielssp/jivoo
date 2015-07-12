<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Core\App;
use Jivoo\Core\Module;

/**
 * A loadable authorization module. Subclasses should use the prefix
 * "Authorization".
 */
abstract class LoadableAuthorization extends Module implements IAuthorization {
  /**
   * @var AuthHelper Authentication and authorization helper.
   */
  protected $Auth;

  /**
   * @var array Associative array of default options for module.
   */
  protected $options = array();
  
  /**
   * Construct module.
   * @param App $app Associated application.
   * @param array $options Associative array of options for module.
   * @param AuthHelper $Auth The authentication and authorization helper.
   */
  public final function __construct(App $app, $options = array(), AuthHelper $Auth) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
    $this->Auth = $Auth;
  }
}