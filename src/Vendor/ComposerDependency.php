<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\Parse\ParseInput;
use Composer\Semver\Semver;

/**
 * A composer dependency.
 */
class ComposerDependency implements Dependency {
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var string
   */
  private $constraint;
  
  public function __construct($name, $constraint) {
    $this->name = $name;
    $this->constraint = $constraint;
  }
  
  /**
   * {@inheritDoc}
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * {@inheritDoc}
   */
  public function checkVersion($version) {
    return Semver::satisfies($version, $this->constraint);
  }
}
