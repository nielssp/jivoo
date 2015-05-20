<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Setup\InstallerSnippet;

/**
 * Controller for setting up root user
 * @property-read HtmlHelper $Html Html helper
 * @property-read FormHelper $Form Form helper
 * @property-read ActiveModel $User User model
 * @property-read ActiveModel $Group Group model
 */
class AuthInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Databases', 'ActiveModels');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html', 'Form');

  /**
   * {@inheritdoc}
   */
  protected $models = array('User', 'Group');

  protected function setup() {
    $this->appendStep('create');
  }
  
  /**
   * Action for setting up root user
   * @return Response A response.
   */
  public function create($data) {
    $this->viewData['title'] = tr('Create admin user');

    $rootGroup = $this->Group->where('name = "root"')->first();
    if (!$rootGroup) {
      $rootGroup = $this->Group->create();
      $rootGroup->name = 'root';
      $rootGroup->title = tr('Admin');
      $rootGroup->save();
      $rootGroup->setPermission('*', true);
    }

    if (isset($data)) {
      if (isset($data['skip'])) {
        return $this->next();
      }
      else {
        $user = $this->User->create($data);
        $user->group = $$rootGroup;
        if ($user->save()) {
          return $this->next();
        }
      }
    }
    else {
      $user = $this->User->create();
      $user->username = 'Admin';
    }

    return $this->render();
  }

}
