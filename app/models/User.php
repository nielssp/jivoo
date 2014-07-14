<?php
class User extends ActiveModel implements IUserModel {

  protected $modules = array('AccessControl');
  
  protected $mixins = array(
    'Timestamps'
  );

  protected $hasMany = array(
    'sessions' => 'Session'
  );

  protected $belongsTo = array(
    'Group',
  );

  protected $validate = array(
    'username' => array(
      'presence' => true,
    ),
    'password' => array(
      'presence' => true,
    ),
    'email' => array(
      'presence' => true,
      'email' => true
    ),
    'confirmPassword' => array(
      'ruleConfirm' => array(
        'callback' => 'confirmPassword',
        'message' => 'The two passwords are not identical'
      ),
    ),
  );

  protected $labels = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirmPassword' => 'Confirm password',
  );

  protected $actions = array(
    'edit' => 'Admin::Users::edit::%id%',
  );
  
  protected $virtual = array(
    'confirmPassword'
  );

  public function recordHasPermission(ActiveRecord $record, $permission) {
    $group = $record->group;
    return isset($group) and $group->hasPermission($permission);
  }

  public function confirmPassword(ActiveRecord $record, $field) {
    if ($record->hasChanged('password'))
      return $record->password == $record->confirmPassword;
    return true;
  }
  
  public function beforeSave(ActiveModelEvent $event) {
    if ($event->record->hasChanged('password')) {
      $hasher = $this->m->AccessControl->getPasswordHasher();
      $event->record->password = $hasher->hash($event->record->password); 
    }
  }
  
  public function createSession(ActiveRecord $user, $validUntil) {
    $session = $user->sessions->create();
    $session->id = Utilities::randomString(32);
    $session->validUntil = $validUntil;
    if ($session->save())
      return $session->id;
    return null;
  }

  
  public function openSession($sessionId) {
    $session = $this->getDatabase()->Session->find($sessionId);
    if ($session) {
      if (!$session->hasExpired())
        return $session->user;
      $session->delete();
    }
    return null;
  }
  
  public function renewSession($sessionId, $validUntil) {
    $session = $this->getDatabase()->Session->find($sessionId);
    if ($session) {
      $session->validUntil = $validUntil;
      $session->save();
    }
  }

  
  public function deleteSession($sessionId) {
    $this->getDatabase()->Session->find($sessionId)->delete();
  }
}
