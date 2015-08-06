<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Models\Form;
use Jivoo\Models\DataType;

/**
 * Attempts to install the lock file and enter maintenance mode.
 *
 */
class LockInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('configure');
  }

  /**
   * Checks if the lock is already in place and enabled.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function check($data = null) {
    $lock = $this->m->Setup->getLock();
    if ($lock->get('enable', false)) {
      return $this->end();
    }
    if (isset($lock['username']) and isset($lock['password'])) {
      if ($this->m->Setup->lock())
        return $this->end();
      else
        throw new SetupException(tr('Installation required. Could not enter maintenance mode.'));
    }
    return $this->next();
  }

  /**
   * Set up maintenance user and install lock.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
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
          return $this->next(); 
        }
      }
    }
    $this->viewData['user'] = $form;
    return $this->render();
  }
}