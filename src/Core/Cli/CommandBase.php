<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cli;

use Jivoo\Core\Module;

/**
 * A command with subcommands.
 */
abstract class CommandBase extends Module implements ICommand {
  
  protected $commands = array();
  
  protected $availableOptions = array(
    'help' => false
  );
  
  protected $shortOptions = array(
    'h' => 'help'
  );
  
  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->availableOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function getShort($option) {
    $keys = array_keys($this->shortOptions, array($option));
    if (isset($keys[0]))
      return $keys[0];
    return null;
  }
  
  /**
   * 
   * @param string $name
   * @param ICommand|callable $command Command or callable.
   * @param string $description Optional description of $command is a callable.
   */
  public function addCommand($name, $command, $description = null) {
    if (!($command instanceof ICommand)) {
      $command = new CallbackCommand($command, $description);
    }
    foreach ($command->getOptions() as $option => $hasParameter)
      $this->addOption($option, $command->getShort($option), $hasParameter);
    $this->commands[$name] = $command;
  }
  
  public function addOption($option, $short = null, $hasParameter = false) {
    $this->availableOptions[$option] = $hasParameter;
    if (isset($short))
      $this->shortOptions[$short] = $option;
  }
  
  public function evalCommand($command) {
    if (is_string($command))
      $parameters = explode(' ', $command); // TODO: use regex
    else
      $parameters = $command;
    $command = array_shift($parameters);
    if ($command == 'exit')
      $this->stop();
    if (!isset($this->commands[$command])) {
      $this->shell->put(tr('Unknown command: %1', $command));
      return;
    }
    call_user_func($this->commands[$command], $parameters, $this->options);
  }
  
  public function getDescription($option = null) {
    return null;
  }
  
  public function onEmpty() {
    return $this->help();
  }
  
  public function help() {
    $description = $this->getDescription();
    if (isset($description))
      $this->shell->put($description);
    if (count($this->availableOptions)) {
      $this->shell->put(tr('Options:'));
      $options = $this->availableOptions;
      ksort($options);
      foreach ($options as $option => $hasParam) {
        $this->shell->put('  --' . sprintf('% -15s', $option) . ' ' . $this->getDescription($option));
      }
    }
    if (count($this->commands)) {
      $this->shell->put(tr('Commands:'));
      $commands = $this->commands;
      ksort($commands);
      foreach ($commands as $name => $command) {
        $this->shell->put('  ' . sprintf('% -15s', $name) . ' ' . $command->getDescription());
      }
    }
  }
    
  public function __invoke(array $parameters, array $options) {
    if (count($parameters) == 0)
      return $this->onEmpty();
  }
}