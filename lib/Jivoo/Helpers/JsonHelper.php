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
    Http::setContentType('application/json');
    echo json_encode($response);
  }
}
