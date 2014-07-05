<?php
class FormAuthentication extends LoadableAuthentication {
  
  private $cookie = false;
  
  protected $options = array(
  	'username' => 'username',
    'password' => 'password'
  );
  
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher) {
    $this->cookie = isset($data['remember']);
    $user = $userModel->where($this->options['username'] . ' = %s', $data['username'])
      ->first();
    if ($user) {
      $passwordField = $this->options['password'];
      if ($hasher->compare($data['password'], $user->$passwordField))
        return $user;
    }
    return null;
  }
  
  public function cookie() {
    return $this->cookie;
  }
}
