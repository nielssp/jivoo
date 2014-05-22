<?php
class ActionAuthorization extends LoadableAuthorization {
  public function authorize(AuthorizationRequest $authRequest) {
    return $this->Auth->hasPermission(
      $authRequest->controller->getName() . '.' . $authRequest->action
    );
  }
}