<?php
/**
 * JSON Helper
 * @package Core\Helpers
 */
class JsonHelper extends Helper {
  
  protected $modules = array('Templates');
  
  /**
   * Create a JSON response
   * @param mixed Data
   */
  public function respond($response) {
    $this->m->Templates->view->json = json_encode($response);
    $this->m->Templates->view->display('default.json');
  }
}
