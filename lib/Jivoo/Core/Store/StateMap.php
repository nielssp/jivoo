<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * An object allowing for the creation and reading of state-files in a directoy.
 */
class StateMap {
  /**
   * @var string
   */
  private $dir;
  
  /**
   * @var FileStore[]
   */
  private $files = array();
  
  /**
   * Construct state map.
   * @param string $dir State directory.
   */
  public function __construct($dir) {
    $this->dir = $dir;
  }
  
  /**
   * Touch a state document (make sure that it exists).
   * @param string $key State document key.
   */
  public function touch($key) {
    if (!isset($this->files[$key])) {
      $this->files[$key] = new PhpStore($this->dir . '/' . $key . '.php');
      $this->files[$key]->touch();
    }
  }

  /**
   * Open a state document for reading. Remember to call {@see State::close()}
   * on the object when done!
   * @param string $key State document key.
   * @return State State document.
   */
  public function read($key) {
    if (!isset($this->files[$key]))
      $this->touch($key);
    return new State($this->files[$key], false);
  }

  /**
   * Open a state document for reading and writing. Remember to call
   * {@see State::close()} on the object when done!
   * @param string $key State document key.
   * @return State State document.
   */
  public function write($key) {
    if (!isset($this->files[$key]))
      $this->touch($key);
    return new State($this->files[$key], true);
  }
}