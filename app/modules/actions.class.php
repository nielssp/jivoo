<?php
/*
 * Class for acting on url actions
 *
 * @package PeanutCMS
 */

/**
 * Actions class
 */
class Actions implements IModule {

  private $http;

  public function getHttp() {
    return $this->http;
  }

  public function __construct(Http $http) {
    $this->http = $http;
  }

  public static function getDependencies() {
    return array('http');
  }

  /**
   * Check if an action is present in the url and/or post data
   *
   * @param string $action Action
   * @param string $getPost Optional; confine to GET ('get') or POST ('post'), default is 'both'
   * @return bool
   */
  public function has($action, $getPost = 'both') {
    global $PEANUT;
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
    global $PEANUT;
    unset($_GET[$action]);
    return $this->http->getLink(null, array_merge($_GET, array($action => '')));
  }

}