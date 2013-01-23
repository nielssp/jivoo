<?php
// Module
// Name           : Routing
// Version        : 0.3.0
// Description    : The ApakohPHP routing system
// Author         : apakoh.dk
// Dependencies   : Configuration

/**
 * Routing module
 *
 * @package Arachis
 * @subpackage Routing
 */
class Routing extends ModuleBase {
  protected function init() {
    // Set default settings
    $this->m
      ->Configuration
      ->setDefault(
        array('http.rewrite' => 'off', 'http.index.path' => 'posts'));

    $this->request = new Request();

    // Determine if the current URL is correct
    if ($this->m
      ->Configuration
      ->get('http.rewrite') == 'on') {
      if (isset($this->request
        ->path[0]) AND $this->request
            ->path[0] == 'index.php') {
        array_shift($this->request
          ->path);
        $this->redirectPath($this->request
            ->path, $this->request
            ->query);
      }
    }
    else {
      if (!isset($this->request
        ->path[0]) OR $this->request
            ->path[0] != 'index.php') {
        $this->redirectPath($this->request
            ->path, $this->request
            ->query);
      }
      $path = $this->request
        ->path;
      array_shift($path);
      $this->request
        ->path = $path;
    }

    $path = explode('/', $this->m
      ->Configuration
      ->get('http.index.path'));
    $query = $this->m
      ->Configuration
      ->get('http.index.query', true);
    if (count($this->request
      ->path) < 1) {
      $this->request
        ->path = $path;
      $this->request
        ->query = array_merge($query, $this->request
        ->query);
    }
    else if ($path == $this->request
          ->path) {
      $this->redirectPath(array(), $this->request
          ->query);
    }
  }

  /**
   * Redirect the user to another page
   *
   * @todo Might require an absolute URI in some cases? Look into that
   *
   * @param int $status HTTP status code, should be 3xx e.g. 301 for Moved Permanently
   * @param string $location The page to redirect to
   * @thows Exception If status code is invalid
   */
  public static function httpRedirect($status, $location) {
    if (!Routing::setStatus($status)) {
      throw new Exception(tr('Invalid status code: %1', $status));
    }
    header('Location: ' . $location);
    exit;
  }

  /**
   * Set HTTP status code
   * @param int $status Status code
   * @return boolean False if unknown status code, true otherwise
   */
  public static function setStatus($status) {
    $phrase = Routing::statusPhrase($status);
    if ($phrase === false)
      return false;
    header('HTTP/1.1 ' . $status . ' ' . $phrase);
    return true;
  }

  /**
   * Returns the phrase for a HTTP status code
   *
   * @param int $status HTTP status code
   * @return string|false Phrase or false if unknown status code
   */
  public static function statusPhrase($status) {
    switch ($status) {
      case 200:
        return 'OK';
      case 300:
        return 'Multiple Choices';
      case 301:
        return 'Moved Permanently';
      case 302:
        return 'Found';
      case 303:
        return 'See Other';
      case 304:
        return 'Not Modified';
      case 305:
        return 'Use Proxy';
      case 306:
        return 'Switch Proxy';
      case 307:
        return 'Temporary Redirect';
      case 404:
        return 'Not Found';
    }
    return false;
  }
}
