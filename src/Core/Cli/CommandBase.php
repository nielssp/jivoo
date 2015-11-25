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
abstract class CommandBase extends Module implements Command {
  
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
   * @param Command|callable $command Command or callable.
   * @param string $description Optional description of $command is a callable.
   */
  public function addCommand($name, $command, $description = null) {
    if (!($command instanceof Command)) {
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
      $this->m->shell->put(tr('Unknown command: %1', $command));
      return;
    }
    call_user_func($this->commands[$command], $parameters, $this->options);
  }
  

  /**
   * Print a line of text to standard error.
   * @param string $line Line.
   * @param string $eol Line ending, set to '' to prevent line break.
   */
  protected function error($line, $eol = PHP_EOL) {
    $this->m->shell->error($line, $eol);
  }
  
  /**
   * Print a line of text to standard output.
   * @param string $line Line.
   * @param string $eol Line ending, set to '' to prevent line break.
   */
  protected function put($line = '', $eol = PHP_EOL) {
    $this->m->shell->put($line, $eol);
  }
  
  /**
   * Read a line of user input from standard input. Uses {@see readline} if
   * available.
   * @param string $prompt Optional prompt.
   * @return string User input.
   */
  protected function get($prompt = '') {
    $this->m->shell->get($prompt);
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
      $this->m->shell->put($description);
    if (count($this->availableOptions)) {
      $this->m->shell->put(tr('Options:'));
      $options = $this->availableOptions;
      ksort($options);
      foreach ($options as $option => $hasParam) {
        $this->m->shell->put('  --' . sprintf('% -15s', $option) . ' ' . $this->getDescription($option));
      }
    }
    if (count($this->commands)) {
      $this->m->shell->put(tr('Commands:'));
      $commands = $this->commands;
      ksort($commands);
      foreach ($commands as $name => $command) {
        $this->m->shell->put('  ' . sprintf('% -15s', $name) . ' ' . $command->getDescription());
      }
    }
  }
    
  public function __invoke(array $parameters, array $options) {
    if (count($parameters) == 0)
      return $this->onEmpty();
    $command = array_shift($parameters);
    if (!isset($this->commands[$command])) {
      $this->m->shell->put(tr('Unknown command: %1', $command));
      return;
    }
    call_user_func($this->commands[$command], $parameters, $options);
  }
}