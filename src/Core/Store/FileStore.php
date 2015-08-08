<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Stores data in files. See subclasses for implementations of file formats.
 */
abstract class FileStore implements IStore {
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
   * @var string Default file content (used by {@see touch()}).
   */
  protected $defaultContent = '';
  
  /**
   * Construct file store.
   * @param string $file File path.
   */
  public function __construct($file) {
    $this->file = $file;
  }
  
  /**
   * Ensures that the file is unlocked.
   */
  public function __destruct() {
    if (isset($this->handle))
      flock($this->handle, LOCK_UN);
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
   * Touch the file (attempt to create it if it doesn't exist).
   * @return boolean True if file exists and is writable, false otherwise.
   */
  public function touch() {
    if (file_exists($this->file))
      return true;
    $handle = fopen($this->file, 'c');
    if (!$handle)
      return false;
    fwrite($handle, $this->defaultContent);
    fclose($handle);
    return true;
  }
  
  /**
   * {@inheritdoc}
   */
  public function open($mutable = false) {
    $handle = @fopen($this->file, $mutable ? 'c+' : 'r');
    if (!$handle)
      throw new AccessException(tr('Could not open file: %1', $this->file));
    $noBlock = $this->blocking ? 0 : LOCK_NB;
    if (!flock($handle, ($mutable ? LOCK_EX : LOCK_SH) | $noBlock)) {
      fclose($handle);
      throw new LockException(tr('Could not lock file: %1', $this->file));
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
    clearstatcache($this->file); // fixes wrong filesize() etc.
    $this->data = null;
    $this->handle = null;
    $this->mutable = null;
  }
  
  /**
   * Encode data for file output.
   * @param array $data Data.
   * @return string File content.
   */
  protected abstract function encode(array $data);
  
  /**
   * Decode file content.
   * @param string $content File content.
   * @return array Data.
   * @throws AccessException If data format is invalid.
   */
  protected abstract function decode($content);

  /**
   * {@inheritdoc}
   */
  public function read() {
    if (isset($this->data))
      return $this->data;
    if (!isset($this->handle))
      return;
    $content = fread($this->handle, filesize($this->file));
    $this->data = $this->decode($content);
    if (!is_array($this->data)) {
      $this->data = null;
      throw new AccessException(tr('Invalid file format: %1', $this->file));
    }
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function write(array $data) {
    if (!isset($this->handle))
      return;
    if (!$this->mutable)
      throw new AccessException(tr('Not mutable'));
//     Logger::debug(tr('Write file: %1', $this->file));
    $this->data = $data;
    ftruncate($this->handle, 0);
    rewind($this->handle);
    fwrite($this->handle, $this->encode($data));
    fflush($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen() {
    return isset($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function isMutable() {
    if (!isset($this->handle))
      return false;
    return $this->mutable;
  }
}