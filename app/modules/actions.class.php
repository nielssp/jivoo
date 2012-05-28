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
class Actions implements IModule {

  private $core;
  private $http;

  public function __construct(Core $core) {
    $this->core = $core;
    $this->http = $core->http;
  }
  /**
   * Check if an action is present in the url and/or post data
   *
   * @param string $action Action
   * @param string $getPost Optional; confine to GET ('get') or POST ('post'), default is 'both'
   * @return bool
   */
  public function has($action, $getPost = 'both') {
    $path = $this->http->getPath();
    $params = $this->http->getParams();
    if ($getPost != 'post' AND $getPost != 'sessionget' AND isset($params[$action])) {
      $this->http->unsetParam($action);
      return true;
    }
    if ($getPost != 'get' AND $getPost != 'sessionget' AND isset($_POST['action']) AND $_POST['action'] == $action) {
      return true;
    }
    if ($getPost == 'sessionget' AND isset($_SESSION[SESSION_PREFIX . 'action']) AND $_SESSION[SESSION_PREFIX . 'action'] == $action) {
      unset($_SESSION[SESSION_PREFIX . 'action']);
      if (isset($params[$action])) {
        $this->http->unsetParam($action);
        return true;
      }
    }
    return false;
  }

  public function add($action) {
    unset($_GET[$action]);
    return $this->http->getLink(null, array_merge($_GET, array($action => '')));
  }

}
