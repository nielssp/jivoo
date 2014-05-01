<?php
// Module
// Name           : Authentication
// Description    : The Jivoo authentication system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Shadow
//                  Jivoo/Setup Jivoo/Templates Jivoo/Database
//                  Jivoo/Routing Jivoo/Helpers Jivoo/Models

/**
 * Authentication module
 *
 * @package Jivoo\Authentication
 */
class Authentication extends ModuleBase {
  
  /**
   * @var string[] List of supported hash types
   */
  private $hashTypes = array(
    'sha512', 'sha256', 'blowfish',
    'md5', 'ext_des', 'std_des'
  );
  
  /**
   * @var User Current user if logged in
   */
  private $user = null;
  
  /**
   * @var UserSession Current user session if logged in
   */
  private $userSession = null;

  /**
   * @var Group The group associated with unregisterd visitors (guests)
   */
  private $unregistered = null;
  
  /**
   * @var int Number of seconds a session is valid
   */
  private $sessionLifetime = 0;
  
  /**
   * @var int Number of seconds a long session is vaid
   */
  private $longSessionLifetime = 0;
  
  /**
   * @var int Number of seconds after which a short session is renewed
   */
  private $renewSessionAfter = 0;

  protected function init() {
    if (!isset($this->config['hashType'])) {
      foreach ($this->hashTypes as $hashType) {
        $constant = 'CRYPT_' . strtoupper($hashType);
        if (defined($constant) AND constant($constant) == 1) {
          $this->config['hashType'] = $hashType;
          break;
        }
      }
    }
    
    $this->config->defaults = array(
      'defaultGroups' => array(
        'unregistered' => 'guests',
        'registered' => 'users',
      ),
      'rootCreated' => false, 
      'sessionLifetime' => 60 * 30, // 30 minutes
      'longSessionLifetime' => 60 * 60 * 24 * 14, // 14 days
      'renewSessionAfter' => 60 * 5, // 5 minutes 
    );
    
    $this->m->Database->addSchemaIfMissing('User', $this->p('default/schemas/UserSchema.php'));
    $this->m->Database->addSchemaIfMissing('Session', $this->p('default/schemas/SessionSchema.php'));
    $this->m->Database->addSchemaIfMissing('Group', $this->p('default/schemas/GroupSchema.php'));
    $this->m->Database->addSchemaIfMissing('GroupPermission', $this->p('default/schemas/GroupPermissionSchema.php'));
    
    $this->m->Database->addActiveModelIfMissing('User', $this->p('default/models/User.php'));
    $this->m->Database->addActiveModelIfMissing('Session', $this->p('default/models/Session.php'));
    $this->m->Database->addActiveModelIfMissing('Group', $this->p('default/models/Group.php'));
    
    $this->sessionLifetime = $this->config['sessionLifetime']; 
    $this->longSessionLifetime = $this->config['longSessionLifetime'];
    $this->renewSessionAfter = $this->config['renewSessionAfter'];

    $rootGroup = null;
    if ($this->m->Database->isNew('Group')) {
      $group = $this->m->Models->Group->create();
      $group->name = 'root';
      $group->title = tr('Admin');
      $group->save();
      $group->setPermission('*', true);
      $rootGroup = $group;

      $group = $this->m->Models->Group->create();
      $group->name = 'users';
      $group->title = tr('User');
      $group->save();
      $group->setPermission('frontend', true);

      $group = $this->m->Models->Group->create();
      $group->name = 'guests';
      $group->title = tr('Guest');
      $group->save();
      $group->setPermission('frontend', true);

    }

    if ($this->m->Database->isNew('User') OR $this->config['rootCreated'] !== true) {
      $this->config['rootCreated'] = false;
      Logger::debug('Authentication: No root user created');
      $controller = new AuthenticationSetupController(
        $this->m->Routing, $this->m->Templates, $this->config
      );
      $controller->addModule($this->m->Shadow);
      $this->m->Helpers->addHelpers($controller);
      $this->m->Models->addModels($controller);
      $this->view->addTemplateDir($this->p('templates'), 3);
      $controller->rootGroup = $rootGroup;
      $this->m->Setup->enterSetup($controller, 'setupRoot');
    }
    
    $authHelper = new AuthHelper($this->m->Routing);
    $authHelper->addModule($this);
    $authHelper->addModule($this->m->Shadow);
    $authHelper->addHelper($this->m->Helpers->Json);
    $this->m->Helpers->addHelper($authHelper);

    if (!$this->isLoggedIn()) {
      $unregistered = $this->m->Models->Group
        ->where('name = ?', $this->config['defaultGroups']['unregistered'])
        ->first();
      if ($unregistered) {
        $this->unregistered = $unregistered;
      }
    }
  }


  /**
   * Generate a random salt for a specific hash
   * @uses mt_rand() for random numbers
   * @param string $hashType Hash type, if not set the configuration will be
   * used to determine hash type.
   * @return string Random salt
   */
  public function genSalt($hashType = null) {
    if (!isset($hashType)) {
      $hashType = $this->config['hashType'];
      if ($hashType == 'auto') {
        foreach ($this->hashTypes as $t) {
          $constant = 'CRYPT_' . strtoupper($t);
          if (defined($constant) AND constant($constant) == 1) {
            $hashType = $t;
          }
        }
      }
    }
    switch (strtolower($hashType)) {
      case 'sha512':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$6$rounds=5001$';
        break;
      case 'sha256':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$5$rounds=5001$';
        break;
      case 'blowfish':
        $saltLength = 22;
        // cost (second param) from 04 to 31
        $prefix = '$2a$09$';
        break;
      case 'md5':
        $saltLength = 8;
        $prefix = '$1$';
        break;
      case 'ext_des':
        $saltLength = 4;
        // iterations (4 characters after _) from .... to zzzz
        $prefix = '_J9..';
        break;
      case 'std_des':
      default:
        $saltLength = 2;
        $prefix = '';
        break;
    }
    return $prefix . $this->genUid($saltLength);
  }
  
  /**
   * Hash a string
   * @uses crypt() to hash string
   * @param string $string String to hash
   * @param string $hashType Hash type, if not set the configuration will be
   * used to determine hash type.
   * @return string Hashed string
   */
  public function hash($string, $hashType = null) {
    return crypt($string, $this->genSalt($hashType));
  }
  
  /**
   * Compare an unhashed string with a hashed string
   * @uses crypt() to hash string
   * @param string $string Unhashed string
   * @param string $hash Hashed string
   * @return boolean True if the two strings are equal, false otherwise
   */
  public function compare($string, $hash) {
    return crypt($string, $hash) == $hash;
  }
  
  /**
   * Check if anyone is logged in
   * @return boolean True if logged in, false otherwise
   */
  public function isLoggedIn() {
    if (isset($this->user)) {
      return true;
    }
    if ($this->checkSession()) {
      return true;
    }
    if ($this->checkCookie()) {
      return true;
    }
    return false;
  }

  /**
   * Check current user or guest for a permission
   * @param string $permission Permission key
   * @return boolean True if user has permission, false otherwise
   */
  public function hasPermission($permission) {
    if ($this->isLoggedIn()) {
      return $this->user->hasPermission($permission);
    }
    else if (isset($this->unregistered)) {
      return $this->unregistered->hasPermission($permission);
    }
    else {
      return false;
    }
  }
  
  /**
   * Get default group for registered users
   * @return Group|false A group or false if unavailable
   */
  public function getDefaultGroup() {
    return $this->m->Models->Group->first(
      SelectQuery::create()
        ->where('name = ?', $this->config['defaultGroups']['registered'])
    );
  }

  /**
   * Get the current user
   * @return User|false The current user or false if not logged in
   */
  public function getUser() {
    return $this->isLoggedIn() ? $this->user : false;
  }
  
  /**
   * Check session for logged in user
   * @return boolean True if logged in, false otherwise
   */
  protected function checkSession() {
    if (isset($this->session['auth_session'])) {
      $ip = $this->request->ip;
      $session = $this->m->Models->UserSession->find($this->session['auth_session']);
      if ($session) {
        if ($session->hasExpired()) {
          $session->delete();
        }
        else {
          $this->userSession = $session;
          $this->user = $session->getUser();
          if (isset($this->session['auth_renew_at'])) {
            if ($this->session['auth_renew_at'] <= time()) {
              $this->session['auth_renew_at'] = time() + $this->renewSessionAfter;
              $session->valid_until = time() + $this->sessionLifetime;
              if (!$session->save()) {
                // Unable to renew session
              }
            }
          }
          return true;
        }
      }
      unset($this->session['auth_session']);
    }
    return false;
  }

  /**
   * Check cookie for logged in user
   * @return boolean True if logged in, false otherwise
   */
  protected function checkCookie() {
    if (isset($this->request->cookies['auth_session'])) {
      $session = $this->m->Models->UserSession->find($this->request->cookies['auth_session']);
      if ($session) {
        if ($session->hasExpired()) {
          $session->delete();
        }
        else {
          $this->userSession = $session;
          $this->user = $session->getUser();
          return true;
        }
      }
      unset($this->request->cookies['auth_session']);
    }
    return false;
  }

  /**
   * Create session to log user in
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @throws Exception if unable to save user session data
   */
  protected function createSession($remember = false) {
    $ip = $this->request->ip;
    $session = $this->m->Models->UserSession->create();
    $session->id = $this->m->Shadow->genUid();
    $session->setUser($this->user);
    if ($remember) {
      $session->valid_until = time() + $this->longSessionLifetime;
      $this->request->cookies['auth_session'] = $session->id;
    }
    else {
      $session->valid_until = time() + $this->sessionLifetime;
      $this->session['auth_session'] = $session->id;
      $this->session['auth_renew_at'] = time() + $this->renewSessionAfter;
    }
    if (!$session->save()) {
      throw new Exception(tr('Could not create session.'));
    }
    $this->userSession = $session;
  }

  /**
   * Log in to user
   * @param string $username Username
   * @param string $password Password
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @return bool True if successful, false otherwise
   */
  public function logIn($username, $password, $remember = false) {
    $user = $this->m->Models->User
      ->first(SelectQuery::create()->where('username = ?', $username));
    if (!$user) {
      return false;
    }
    if (!(!isset($password) AND $user->password == '')) { 
      if (!$this->m->Shadow->compare($password, $user->password)) {
        return false;
      }
    }
    $this->user = $user;
    $this->createSession($remember);
    return true;
  }

  /**
   * Log out of current user and unset sessions and cookies
   */
  public function logOut() {
    if (isset($this->userSession)) {
      $this->userSession->delete();
    }
    if (isset($this->session['auth_session'])) {
      unset($this->session['auth_session']);
    }
    if (isset($this->request->cookies['auth_session'])) {
      unset($this->request->cookies['auth_session']);
    }
    $this->userSession = null;
    $this->user = null;
  }
  
  /**
   * Garbage collect expired sessions
   */
  public function garbageCollect() {
    return $this->m->Database->usersessions->delete()
      ->where('valid_until <= %d', time())
      ->execute();
  }
}
