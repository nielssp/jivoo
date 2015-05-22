<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Models\Form;
use Jivoo\Models\DataType;

class LockInstaller extends InstallerSnippet {
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('configure');
  }

  public function check($data = null) {
    if ($this->m->Setup->getLock()->get('enable', false))
      return $this->end();
    return $this->next();
  }

  public function configure($data = null) {
    $this->viewData['title'] = tr('Configure maintenance user');
    $form = new Form('user');
    $form->addField('username', DataType::string(), tr('Username'));
    $form->addField('password', DataType::string(), tr('Password'));
    $form->addField('confirmPassword', DataType::string(), tr('Confirm password'));
    $form->username = 'Admin';
    if (isset($data)) {
      $form->addData($data['user']);
      if ($form->isValid()) {
        if ($form->password !== $form->confirmPassword) {
          $form->addError('password', tr('The two passwords are not identical.'));
        }
        else {
          $auth = $this->m->Setup->getAuth();
          $password = $auth->passwordHasher->hash($form->password);
          $this->m->Setup->lock($form->username, $password);
          $auth->login($form->getData());
          return $this->next(); 
        }
      }
    }
    $this->viewData['form'] = $form;
    return $this->render();
  }
}