<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cli;

/**
 * A command, or subcommand, for use in the command-line interface.
 */
interface ICommand {
  /**
   * Get array of options accepted by this command.
   * 
   * An option may also accept a value, e.g. '-f some-file', '-f"some-file"' or
   * '--file some-file'.
   * @return bool[] An associative array where the key is an option name, e.g.
   * 'help' or 'h', and the value is either true (option accepts a value) or
   * false (option does not accept a value).
   */
  public function getOptions();
  
  /**
   * Get the short version of an option if any.
   * @param string $option The long option (as returned by {@see getOptions}),
   * e.g. 'help' or 'file'.
   * @return string|null The short option if available, e.g. 'h' or 'f'.
   */
  public function getShort($option);
  
  /**
   * Get description of command or option.
   * @param string|null $option Option to describe, if null, the method should
   * return a description of the command.
   * @return string|null Description of command or option, or null if not
   * available.
   */
  public function getDescription($option = null);
  
  /**
   * Invoke command.
   * @param string[] $parameters List of parameters for command.
   * @param string[] $options Associative array of options for command,
   */
  public function __invoke(array $parameters, array $options);
}