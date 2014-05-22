<?php

interface IAuthorization {
  public function authorize(AuthorizationRequest $authRequest);
}
