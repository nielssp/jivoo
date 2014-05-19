<?php
class FormAuthentication extends LoadableAuthentication {
  
  private $cookie = false;
  
  protected $options = array(
  	'username' => 'username',
    'password' => 'password'
  );
  
  public function authenticate($data, IUserModel $userModel) {
    $this->cookie = isset($data['remember']);
    return $userModel->where($this->options['username'] . ' = %s', $data['username'])
      ->and($this->options['password'] . ' = %s', $data['password'])->first();
  }
  
  public function cookie() {
    return $this->cookie;
  }
}