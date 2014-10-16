<?php
class ReCaptcha extends ExtensionModule implements IFormExtension {
  
  private $Form = null;
  
  private $publicKey = null;
  private $privateKey = null;
  private $error = null;
  
  protected function init() {
    if (isset($this->config['publicKey']))
      $this->publicKey = $this->config['publicKey'];
    if (isset($this->config['privateKey']))
      $this->publicKey = $this->config['privateKey'];
    
    if ($_POST["recaptcha_response_field"]) {
        $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

        if ($resp->is_valid) {
                echo "You got it!";
        } else {
                # set the error code so that we can display it
                $this->error = $resp->error;
        }
    }
  }
  
  public function prepare() {
    $this->Form = $this->view->data->Form;
    require_once $this->p('recaptchalib.php');
    return true;
  }
  public function label($label = null, $attributes = array()) {
    return '<label>' . tr('Captcha') . '</label>';
  }
  public function ifRequired($output) {
    return $output;
  }
  public function field($attributes = array()) {
    if (!isset($this->publicKey) or !isset($this->privateKey))
      return tr('Not configured. Missing private and public key.');
    return recaptcha_get_html($this->publickey, $this->error);
  }
  public function error($default = '') {
    return $default;
  }
}