<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Core\App;
use Jivoo\Core\Module;

/**
 * A loadable authentication module. Subclasses should use the prefix
 * "Authentication".
 */
abstract class LoadableAuthentication extends Module implements IAuthentication {
  /**
   * @var array Associative array of default options for module.
   */
  protected $options = array();

  /**
   * Construct module.
   * @param App $app Associated application.
   * @param array $options Associative array of options for module.
   */
  public final function __construct(App $app, $options = array()) {
    parent::__construct($app);
    $this->options = array_merge($this->options, $options);
  }
  
  /**
   * {@inheritdoc}
   */
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function deauthenticate($user, IUserModel $userModel) { }

  /**
   * {@inheritdoc}
   */
  public function cookie() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function isStateless() {
    return false;
  }
}