<?php
class CallbackAuthorization extends LoadableAuthorization {
  protected $options = array(
  	'method' => 'authorize',
  );
  
  public function authorize(AuthorizationRequest $authRequest) {
    return call_user_func(
      array($authRequest->controller, $this->options['method']),
      $authRequest->action
    );
  }
}