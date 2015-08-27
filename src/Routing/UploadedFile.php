<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\InvalidPropertException;

class UploadedFile {
  private $tmpName;
  private $name;
  private $type;
  private $size;
  private $error;
  
  public function __construct($file, $offset = null) {
    if (isset($offset)) {
      $this->tmpName = $file['tmp_name'][$offset];
      $this->name = $file['name'][$offset];
      $this->type = $file['type'][$offset];
      $this->size = $file['size'][$offset];
      $this->error = $file['error'][$offset];
    }
    else {
      $this->tmpName = $file['tmp_name'];
      $this->name = $file['name'];
      $this->type = $file['type'];
      $this->size = $file['size'];
      $this->error = $file['error'];
    }
  }
  
  public function __get($property) {
    switch ($property) {
      case 'name':
      case 'type':
      case 'size':
      case 'error':
        return $this->$property;
    }
    throw new InvalidPropertyException('Undefined property: ' . $property);
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'name':
      case 'type':
      case 'size':
      case 'error':
        return isset($this->$property);
    }
    throw new InvalidPropertyException('Undefined property: ' . $property);
  }
  
  public function moveTo($path) {
    if (!isset($this->tmpName))
      throw new UploadException('File already moved');
    if (!is_uploaded_file($this->tmpName))
      throw new UploadException('Not an uploaded file');
    if (!move_uploaded_file($this->tmpName, $path))
      throw new UploadException('Could not move file');
    $this->tmpName = null;
  }
  
  public static function convert($files) {
    $result = array();
    foreach ($files as $key => $file) {
      if (isset($file['tmp_name'])) {
        if (is_string($file['tmp_name'])) {
          $result[$key] = new UploadedFile($file);
          continue;
        }
        if (is_array($file['tmp_name'])) {
          if (isset($file['tmp_name'][0]) and is_string($file['tmp_name'][0])) {
            $result[$key] = array();
            foreach ($file['tmp_name'] as $offset => $tmpName) {
              $result[$key][] = new UploadedFile($file, $offset);
            }
            continue;
          }
        }
      }
      $result[$key] = self::convert($file);
    }
    return $result;
  }
}