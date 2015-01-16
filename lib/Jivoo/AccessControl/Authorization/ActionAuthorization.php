<?php
/**
 * Authorize based on name of controller and action, e.g. checks permission
 * "PostsAdmin.view" for the action "view" in "PostsAdminController".
 * Uses access control lists.
 * @package Jivoo\AccessControl\Authorization
 */
class ActionAuthorization extends LoadableAuthorization {
  /**
   * {@inheritdoc}
   */
  public function authorize(AuthorizationRequest $authRequest) {
    Logger::debug('authorize ' . $authRequest->controller->getName() . '.' . $authRequest->action);
    return $this->Auth->hasPermission(
      $authRequest->controller->getName() . '.' . $authRequest->action
    );
  }
}