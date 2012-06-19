<?php
// Module
// Name           : Actions
// Version        : 0.2.0
// Description    : The PeanutCMS action system
// Author         : PeanutCMS
// Dependencies   : http
/*
 * Class for acting on url actions
 *
 * @package PeanutCMS
 */

/**
 * Actions class
 */
class Actions extends ModuleBase {

  private $request;

  protected function init() {
    $this->request = $this->m->Http->getRequest();
  }
  /**
   * Check if an action is present in the url and/or post data
   *
   * @param string $action Action
   * @param string $getPost Optional; confine to GET ('get') or POST ('post'), default is 'both'
   * @return bool
   */
  public function has($action, $getPost = 'both') {
    $path = $this->request->path;
    $query = $this->request->query;
    $post = $this->request->data;
    if ($getPost != 'post' AND $getPost != 'sessionget' AND isset($query[$action])) {
      $this->request->unsetQuery($action);
      return true;
    }
    if ($getPost != 'get' AND $getPost != 'sessionget' AND isset($_POST['action']) AND $_POST['action'] == $action) {
      return true;
    }
    if ($getPost == 'sessionget' AND isset($_SESSION[SESSION_PREFIX . 'action']) AND $_SESSION[SESSION_PREFIX . 'action'] == $action) {
      unset($_SESSION[SESSION_PREFIX . 'action']);
    }
    return false;
  }

  public function add($action) {
    unset($_GET[$action]);
    return $this->m->Http->getLink(null, array_merge($_GET, array($action => '')));
  }

}
