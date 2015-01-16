<?php
/**
 * JSON Helper.
 * @package Jivoo\Helpers
 */
class JsonHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('View');
  
  /**
   * Create a JSON response.
   * @param mixed Data.
   */
  public function respond($response) {
    return new TextResponse(Http::OK, 'json', json_encode($response));
  }
}
