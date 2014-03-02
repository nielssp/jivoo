<?php
/**
 * Controller for setting up root user
 * @package Core\Authentication
 * @property-read HtmlHelper $Html Html helper
 * @property-read FormHelper $Form Form helper
 * $property-read ActiveModel $User User model
 * $property-read ActiveModel $Group Group model
 */
class AuthenticationSetupController extends SetupController {

  protected $helpers = array('Html', 'Form');

  protected $models = array('Users', 'Groups');

  /**
   * Action for setting up root user
   */
  public function setupRoot() {
    $this->title = tr('Welcome to %1', $this->config->parent['app']['name']);

    if (!isset($this->rootGroup)) {
      $this->rootGroup = $this->Groups->where('name = "root"')->first();
      if (!$this->rootGroup) {
        $this->rootGroup = $this->Groups->create();
        $this->rootGroup->name = 'root';
        $this->rootGroup->title = tr('Admin');
        $this->rootGroup->save();
        $this->rootGroup->setPermission('*', true);
      }
    }

    if ($this->request->hasValidData()) {
      if (isset($this->request->data['skip'])) {
        $this->config->set('rootCreated', true);
        if ($this->config->save()) {
          $this->redirect(null);
        }
        else {
          return $this->saveConfig();
        }
      }
      else {
        $this->user = $this->Users->create($this->request->data['Users']);  
        if ($this->user->isValid()) {
          $this->user->password = $this->m->Shadow->hash($this->user->password);
          $this->user->group = $this->rootGroup;
          $this->user->save(array('validate' => false));
          $this->config->set('rootCreated', true);
          if ($this->config->save()) {
            $this->redirect(null);
          }
          else {
            return $this->saveConfig();
          }
        }
      }
    }
    else {
      $this->user = $this->Users->create();
      $this->user->username = 'root';
    }

    return $this->render();
  }

}
