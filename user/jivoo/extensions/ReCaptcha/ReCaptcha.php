<?php
class ReCaptcha extends ExtensionModule implements IFormExtension {
  
  protected $modules = array('Models');
  
  private $Form = null;
  
  private $publicKey = null;
  private $privateKey = null;
  private $error = null;
  
  private $imported = false;
  
  protected function init() {
    if (isset($this->config['publicKey']))
      $this->publicKey = $this->config['publicKey'];
    if (isset($this->config['privateKey']))
      $this->privateKey = $this->config['privateKey'];
    
    if (isset($this->publicKey) and isset($this->privateKey)) {
      $this->m->Models->Comment->addVirtual('reCaptcha');
      $validator = $this->m->Models->Comment->getValidator();
      $validator->reCaptcha->callback = array($this, 'validate');
    }
  }
  
  private function importLib() {
    if (!$this->imported) {
      require $this->p('recaptchalib.php');
      $this->imported = true;
    }
  }
  
  public function validate(ActiveRecord $record, $field) {
    if (isset($this->request->data['recaptcha_response_field'])
        and isset($this->request->data['recaptcha_challenge_field'])) {
      $this->importLib();
      $resp = recaptcha_check_answer(
        $this->privateKey, $this->request->ip,
        $this->request->data['recaptcha_challenge_field'],
        $this->request->data['recaptcha_response_field']
      );
      if ($resp->is_valid)
        return true;
      $this->error = $resp->error;
    }
    return tr('Invalid captcha.');
  }
  
  public function prepare() {
    $this->Form = $this->view->data->Form;
    $this->importLib();
    $this->view->blocks->append(
      'body-top',
      '<script type="text/javascript">'
        . 'var RecaptchaOptions = { theme : "clean" };'
        . '</script>' . PHP_EOL
    );
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
    return recaptcha_get_html($this->publicKey, $this->error);
  }
  public function error($default = '') {
    return $this->Form->error('reCaptcha', $default);
  }
}