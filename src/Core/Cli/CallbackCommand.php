<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cli;

/**
 * A command based on a callback.
 */
class CallbackCommand implements Command {
  /**
   * @var callable
   */
  private $callable;
  
  /**
   * @var string|null
   */
  private $description;
  
  /**
   * Construct callback command.
   * @param callable $callable Function.
   * @param string|null $description Optional description.
   */
  public function __construct($callable, $description = null) {
    $this->callable = $callable;
    $this->description = $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return array();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getShort($option) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription($option = null) {
    if (!isset($option))
      return $this->description;
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke(array $parameters, array $options) {
    return call_user_func($this->callable, $parameters, $options);
  }
}