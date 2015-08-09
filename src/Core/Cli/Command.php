<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cli;

use Jivoo\Core\Module;
use Jivoo\Core\App;

/**
 * Command-line interface for Jivoo applications.
 */
class Command extends Module implements ICommand {
  
  private $name;
  
  private $lastError = null;
  
  private $commands = array();
  
  private $options = array();
  
  private $availableOptions = array();
  
  private $shortOptions = array();
  
  public function __construct(App $app) {
    parent::__construct($app);
    $this->addCommand('version', array($this, 'showVersion'));
    $this->addCommand('help', array($this, 'showHelp'));
    $this->addCommand('trace', array($this, 'showTrace'));
    $this->addOption('help', 'h');
    $this->addOption('trace', 't');
  }
  
  public function addCommand($command, $function) {
    $this->commands[$command] = $function;
  }
  
  public function addOption($option, $short = null, $hasParameter = false) {
    $this->availableOptions[$option] = $hasParameter;
    if (isset($short))
      $this->shortOptions[$short] = $option;
  }
  
  
  public function hasOption($option) {
    return isset($this->options[$option]);
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
      $this->put(tr('Unknown command or subsystem: %1', $command));
      return;
    }
    call_user_func_array($this->commands[$command], $parameters);
  }
  
  public function showTrace() {
    if (!isset($this->lastError))
      return;
    $this->dumpException($this->lastError);
  }
  
  public function dumpException(\Exception $exception) {
    $this->put(tr(
      '%1: %2 in %3:%4', get_class($exception),
      $exception->getMessage(), $exception->getFile(), $exception->getLine()
    ));
    $this->put();
    $this->put(tr('Stack trace:'));
    $trace = $exception->getTrace();
    foreach ($trace as $i => $call) {
      $message = '  ' . sprintf('% 2d', $i) . '. ';
      if (isset($call['file'])) {
        $message .=  $call['file'] . ':';
        $message .=  $call['line'] . ' ';
      }
      if (isset($call['class'])) {
        $message .=  $call['class'] . '::';
      }
      $message .=  $call['function'] . '(';
      $arglist = array();
      foreach ($call['args'] as $arg) {
        $arglist[] = (is_scalar($arg) ? var_export($arg, true) : gettype($arg));
      }
      $message .=  implode(', ', $arglist);
      $message .=  ')';
      $this->put($message);
    }
    $previous = $exception->getPrevious();
    if (isset($previous)) {
      $this->put(tr('Caused by:')); 
      $this->dumpException($previous);
    }
  }
  
  public function showVersion() {
    $this->put($this->app->name . ' ' . $this->app->version);
    $this->put('Jivoo ' . \Jivoo\VERSION);
  }
  
  public function showHelp() {
    $this->put('usage: ' . $this->name . ' [COMMAND(S)]');
  }
  
  public function handleException(\Exception $exception) {
    $this->lastError = $exception;
    if ($this->hasOption('trace')) {
      $this->put(tr('Uncaught exception'));
      $this->dumpException($exception);
    }
    else {
      $this->put(tr('Uncaught %1: %2', get_class($exception), $exception->getMessage()));
      $this->put();
      $this->put(tr('Call "trace" to show stack trace'));
    }
  }
  
  public function dump($value) {
    if (is_object($value)) {
      return get_class($value);
    }
    return var_export($value, true);
  }
  
  public function put($line = '') {
    echo $line . PHP_EOL;
  }
  
  public function get($prompt = '') {
    echo $prompt;
    return trim(fgets(STDIN));
  }
  
  public function stop($status = 0) {
    $this->app->stop($status);
  }
  
  public function run() {
    while (ob_get_level() > 0)
      ob_end_clean();
    while (true) {
      try {
        $line = $this->get($this->app->name . '> ');
        if ($line == '')
          continue;
        if ($line[0] == '!') {
          $command = substr($line, 1);
          if ($command == '')
            continue;
          if ($command[0] == '=') {
            $command = substr($command, 1);
            $this->put(' => ' . $this->dump(eval('return ' . $command . ';')));
          }
          else {
            eval($command);
          }
        }
        else if ($line[0] == '$') {
          $this->put(' => ' . $this->dump(eval('return ' . $line . ';')));
        }
        else {
          $this->evalCommand($line);
        }
      }
      catch (\Exception $e) {
        $this->handleException($e);
      }
    }
  }
}