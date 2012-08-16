<?php
// Module
// Name           : Actions
// Version        : 0.2.0
// Description    : The PeanutCMS action system
// Author         : PeanutCMS
// Dependencies   : Http
/*
 * Class for acting on url actions
 *
 * @package PeanutCMS
 */

/**
 * Actions class
 */
class Actions extends ModuleBase {

  protected function init() {
  }
  /**
   * Check if an action is present in the url and/or post data
   *
   * @param string $action Action
   * @param string $getPost Optional; confine to GET ('get') or POST ('post'), default is 'both'
   * @return bool
   */
  public function has($action, $getPost = 'both') {
    trigger_error('Actions:has() is deprecated.', E_USER_DEPRECATED);
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
    trigger_error('Actions:add() is deprecated.', E_USER_DEPRECATED);
    unset($_GET[$action]);
    return $this->m->Http->getLink(null, array_merge($_GET, array($action => '')));
  }

}
