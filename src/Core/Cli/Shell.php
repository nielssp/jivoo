<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cli;

use Jivoo\Core\App;
use Jivoo\Core\Log\ErrorHandler;
use Jivoo\Core\Log\FileHandler;
use Psr\Log\LogLevel;
use Jivoo\Core\Log\StreamHandler;
use Jivoo\Core\Log\ShellHandler;

/**
 * Command-line interface for Jivoo applications.
 */
class Shell extends CommandBase {
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var Exception|null
   */
  private $lastError = null;

  /**
   * @var array
   */
  private $options = array();
  
  public function __construct(App $app) {
    parent::__construct($app);
    $this->addCommand('version', array($this, 'showVersion'), tr('Show the application and framework version'));
    $this->addCommand('help', array($this, 'showHelp'), tr('Show this help'));
    $this->addCommand('trace', array($this, 'showTrace'), tr('Show stack trace for most recent exception'));
    $this->addCommand('exit', array($this, 'stop'), tr('Ends the shell session'));
    $this->addOption('help', 'h');
    $this->addOption('version', 'v');
    $this->addOption('trace', 't');
    $this->addOption('debug', 'd');
  }
  
  public function parseArguments() {
    global $argv;
    $this->name = array_shift($argv);
    
    $command = array();

    $option = null;
    
    foreach ($argv as $arg) {
      if (preg_match('/^--(.*)$/', $arg, $matches) === 1) {
        $o = $matches[1];
        if ($o == '')
          continue;
        if (!isset($this->availableOptions[$o])) {
          $this->put(tr('Unknown option: %1', '--' . $o));
          $this->stop();
        }
        if ($this->availableOptions[$o])
          $option = $o;
        else
          $this->options[$o] = true;
      }
      else if (preg_match('/^-(.+)$/', $arg, $matches) === 1) {
        $options = $matches[1];
        while ($options != '') {
          $o = $options[0];
          if (!isset($this->shortOptions[$o])) {
            $this->put(tr('Unknown option: %1', '-' . $o));
            $this->stop();
          }
          $options = substr($options, 1);
          $o = $this->shortOptions[$o];
          if ($this->availableOptions[$o]) {
            if ($options == '')
              $option = $o;
            else 
              $this->options[$o] = $options;
            break;
          }
          else {
            $this->options[$o] = true;
          }
        }
      }
      else if (isset($option)) {
        $this->options[$option] = $arg;
        $option = null;
      }
      else {
        $command[] = $arg;
      }
    }
    if (isset($this->options['help'])) {
      $this->showHelp();
      exit;
    }
    if (isset($this->options['version'])) {
      $this->showVersion();
      exit;
    }
    if (count($command)) {
      $this->evalCommand($command);
      $this->stop();
    }
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
      $this->error(tr('Unknown command: %1', $command));
      $best = null;
      $bestDist = PHP_INT_MAX;
      foreach ($this->commands as $name => $c) {
        $dist = levenshtein($command, $name);
        if ($dist < $bestDist) {
          $best = $name;
          $bestDist = $dist;
        }
      }
      if ($bestDist < 5)
        $this->put(tr('Did you mean "%1"?', $best));
      return;
    }
    try {
      call_user_func($this->commands[$command], $parameters, $this->options);
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  public function autoComplete($command) {
    $length = strlen($command);
    $results = array();
    foreach ($this->commands as $name => $c) {
      if (strncmp($command, $name, $length) == 0) {
        $results[] = $name;
      }
    }
    return $results;
  }
  
  public function showTrace() {
    if (!isset($this->lastError))
      return;
    self::dumpException($this->lastError);
  }

  public static function dumpException(\Exception $exception, $stream = STDERR) {
    if ($exception instanceof \ErrorException)
      $title = 'Fatal error (' .  ErrorHandler::toString($exception->getSeverity()) . ')';
    else
      $title = get_class($exception);
    fwrite(
      $stream,
      $title . ': ' . $exception->getMessage() . ' in ' .
        $exception->getFile() . ':' . $exception->getLine() . PHP_EOL . PHP_EOL
    );
    fwrite(
      $stream,
      'Stack trace:' . PHP_EOL
    );
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
      if (isset($call['args'])) {
        foreach ($call['args'] as $arg) {
          $arglist[] = (is_scalar($arg) ? var_export($arg, true) : gettype($arg));
        }
        $message .=  implode(', ', $arglist);
      }
      $message .=  ')' . PHP_EOL;
      fwrite($stream, $message);
    }
    $previous = $exception->getPrevious();
    if (isset($previous)) {
      fwrite($stream, 'Caused by:' . PHP_EOL);
      self::dumpException($previous);
    }
    fflush($stream);
  }
  
  public function showVersion() {
    $this->put($this->app->name . ' ' . $this->app->version);
    $this->put('Jivoo ' . \Jivoo\VERSION);
  }
  
  public function showHelp() {
    $this->put('usage: ' . $this->name . ' [options] [command] [args...]');
    $this->help();
  }
  
  public function handleException(\Exception $exception) {
    $this->lastError = $exception;
    if (isset($this->options['trace'])) {
      $this->error(tr('Uncaught exception'));
      self::dumpException($exception);
    }
    else {
      $this->error(tr('Uncaught %1: %2', get_class($exception), $exception->getMessage()));
      $this->put();
      $this->put(tr('Call "trace" or run script with the "--trace" option to show stack trace'));
    }
  }
  
  /**
   * Create a string representation of any PHP value.
   * @param mixed $value Any value.
   * @return string String representation. 
   */
  public function dump($value) {
    if (is_object($value)) {
      return get_class($value);
    }
    if (is_resource($value)) {
      return get_resource_type($value);
    }
    return var_export($value, true);
  }

  /**
   * Print a line of text to standard error.
   * @param string $line Line.
   * @param string $eol Line ending, set to '' to prevent line break.
   */
  public function error($line, $eol = PHP_EOL) {
    fwrite(STDERR, $line . $eol);
    fflush(STDERR);
  }
  
  /**
   * Print a line of text to standard output.
   * @param string $line Line.
   * @param string $eol Line ending, set to '' to prevent line break.
   */
  public function put($line = '', $eol = PHP_EOL) {
    echo $line . $eol;
    flush();
    fflush(STDOUT);
  }
  
  /**
   * Read a line of user input from standard input. Uses {@see readline} if
   * available.
   * @param string $prompt Optional prompt.
   * @return string User input.
   */
  public function get($prompt = '') {
    if (function_exists('readline')) {
      $line = readline($prompt);
      readline_add_history($line);
      return $line;
    }
    $this->put($prompt, '');
    return trim(fgets(STDIN));
  }
  
  /**
   * Stop shell.
   * @param int $status Status code, 0 for success.
   */
  public function stop($status = 0) {
    $this->app->stop($status);
  }
  
  public function run() {
    $level = LogLevel::INFO;
    if (isset($this->options['debug']))
      $level = LogLevel::DEBUG;
    $prompt = $this->app->name . '> ';
    $this->logger->addHandler(new ShellHandler($this, $level));
    while (ob_get_level() > 0)
      ob_end_clean();
    $this->parseArguments();
    if (function_exists('readline_completion_function')) {
      readline_completion_function(array($this, 'autoComplete'));
    }
    while (true) {
      try {
        $line = $this->get($prompt);
        if (!is_string($line)) {
          $this->stop();
          return;
        }
        if ($line === '')
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
