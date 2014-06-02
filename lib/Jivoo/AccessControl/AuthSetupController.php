<?php
/**
 * Controller for setting up root user
* @package Jivoo\Authentication
* @property-read HtmlHelper $Html Html helper
* @property-read FormHelper $Form Form helper
* $property-read ActiveModel $User User model
* $property-read ActiveModel $Group Group model
*/
class AuthSetupController extends SetupController {

  protected $modules = array('Database');
  
  protected $helpers = array('Html', 'Form');

  protected $models = array('User', 'Group');

  /**
   * Action for setting up root user
  */
  public function createUser() {
    $this->title = tr('Welcome to %1', $this->app->name);

    if (!isset($this->rootGroup)) {
      $this->rootGroup = $this->Group->where('name = "root"')->first();
      if (!$this->rootGroup) {
        $this->rootGroup = $this->Group->create();
        $this->rootGroup->name = 'root';
        $this->rootGroup->title = tr('Admin');
        $this->rootGroup->save();
        $this->rootGroup->setPermission('*', true);
      }
    }

    if ($this->request->hasValidData()) {
      if (isset($this->request->data['skip'])) {
        return $this->Setup->done();
      }
      else {
        $this->user = $this->User->create($this->request->data['User']);
        $this->user->group = $this->rootGroup;
        if ($this->user->save()) {
          return $this->Setup->done();
        }
      }
    }
    else {
      $this->user = $this->User->create();
      $this->user->username = 'Admin';
    }

    return $this->render();
  }

}
