<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Core\App;
use Jivoo\Core\Module;

/**
 * A loadable ACL module. Subclasses should use the prefix "Acl".
 * @todo Rename to AclModule or something?
 */
abstract class LoadableAcl extends Module implements IAcl {
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
}