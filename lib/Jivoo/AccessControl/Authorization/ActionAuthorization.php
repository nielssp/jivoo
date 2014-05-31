<?php
class ActionAuthorization extends LoadableAuthorization {
  public function authorize(AuthorizationRequest $authRequest) {
    Logger::debug('authorize ' . $authRequest->controller->getName() . '.' . $authRequest->action);
    return $this->Auth->hasPermission(
      $authRequest->controller->getName() . '.' . $authRequest->action
    );
  }
}