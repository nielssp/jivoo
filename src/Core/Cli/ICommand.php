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
   * Get associative array of short options mapped to long options (defined by
   * {@see getOptions}.
   * 
   * A short option is a shorter alternative to a long option.
   * @return string[] An associative array where the key is a short option,
   * e.g. 'h' or 'f', and the value is the long option, e.g. 'help' or 'file'.
   */
  public function getShortOptions();
  
  /**
   * Run command.
   * @param string[] $args List of parameters for command.
   * @param string[] $options Associative array of options for command,
   */
  public function run($args, $options);
}