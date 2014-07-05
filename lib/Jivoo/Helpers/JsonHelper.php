<?php
/**
 * JSON Helper
 * @package Jivoo\Helpers
 */
class JsonHelper extends Helper {
  
  protected $modules = array('Templates');
  
  /**
   * Create a JSON response
   * @param mixed Data
   */
  public function respond($response) {
    return new TextResponse(Http::OK, 'json', json_encode($response));
  }
}
