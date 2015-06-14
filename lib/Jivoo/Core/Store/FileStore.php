<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

use Jivoo\Core\Config;

/**
 * Stores data in PHP-files.
 */
class FileStore implements IStore {
  /**
   * @var string
   */
  private $file;
  
  /**
   * @var resource
   */
  private $handle = null;
  
  /**
   * @var bool
   */
  private $mutable = null;
  
  /**
   * @var bool
   */
  private $blocking = true;
  
  /**
   * @var array|null
   */
  private $data = null;
  
  /**
   * Construct PHP file store.
   * @param string $file File path.
   */
  public function __construct($file) {
    $this->file = $file;
  }
  
  /**
   * Enable blocking until a lock can be acquired.
   * @param bool $blocking Blocking.
   */
  public function enableBlocking($blocking) {
    $this->blocking = $blocking;
  }
  
  /**
   * Disable blocking, {@see open()} will throw an exception if a lock can't be
   * acquired.
   */
  public function disableBlocking() {
    $this->enableBlocking(false);
  }
  
  /**
   * {@inheritdoc}
   */
  public function open($mutable = false) {
    $handle = fopen($this->file, $mutable ? 'r+' : 'r');
    if (!$handle)
      throw new StoreReadFailedException(tr('Could not open file: %1', $this->file));
    if (!flock($handle, $mutable ? LOCK_EX : LOCK_SH)) {
      fclose($handle);
      throw new StoreLockException(tr('Could not lock file: %1', $this->file));
    }
    $this->handle = $handle;
    $this->mutable = $mutable;
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    if (!isset($this->handle))
      return;
    flock($this->handle, LOCK_UN);
    fclose($this->handle);
    $this->data = null;
    $this->handle = null;
    $this->mutable = null;
  }

  /**
   * {@inheritdoc}
   */
  public function read() {
    if (isset($this->data))
      return $this->data;
    if (!isset($this->handle))
      return;
    $content = file_get_contents($this->file);
    $content = str_replace('<?php', '', $content);
    $this->data = eval($content);
    if (!is_array($this->data)) {
      $this->data = null;
      throw new StoreReadFailedException(tr('Invalid file: %1', $this->file));
    }
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $data) {
    if (!isset($this->handle))
      return;
    if (!$this->mutable)
      throw new StoreWriteFailedException(tr('Not mutable'));
    $this->data = $data;
    ftruncate($this->handle, 0);
    $data = Config::phpPrettyPrint($this->data);
    fwrite($this->handle, '<?php' . PHP_EOL . 'return ' . $data . ';' . PHP_EOL);
    fflush($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function isMutable() {
    if (!isset($this->handle))
      return;
    return $this->mutable;
  }
}