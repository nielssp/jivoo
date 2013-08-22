<?php
/**
 * Controller for setting up root user
 * @package Core\Authentication
 */
class AuthenticationSetupController extends SetupController {

  protected $helpers = array('Html', 'Form');

  protected $models = array('User', 'Group');

  /**
   * Action for setting up root user
   */
  public function setupRoot() {
    $this->title = tr('Welcome to %1', $this->config->parent['app']['name']);

    if (!isset($this->rootGroup)) {
      $this->rootGroup = $this->Group
        ->first(SelectQuery::create()->where('name = "root"'));
      if (!$this->rootGroup) {
        $this->rootGroup = $this->Group->create();
        $this->rootGroup->name = 'root';
        $this->rootGroup->title = tr('Admin');
        $this->rootGroup->save();
        $this->rootGroup->setPermission('*', true);
      }
    }

    if ($this->request->isPost() AND $this->request->checkToken()) {
      if (isset($this->request->data['skip'])) {
        $this->config->set('rootCreated', true);
        if ($this->config->save()) {
          $this->redirect(null);
        }
        else {
          /** @todo goto Setup::saveConfig or something */
          $this->title = '!!! CONFIG ERROR !!!';
        }
      }
      else {
        $this->user = $this->User->create($this->request->data['user']);  
        if ($this->user->isValid()) {
          $this->user->password = $this->m->Shadow->hash($this->user->password);
          $this->user->setGroup($this->rootGroup);
          $this->user->save(array('validate' => false));
          $this->config->set('rootCreated', true);
          if ($this->config->save()) {
            $this->redirect(null);
          }
          else {
            /** @todo goto Setup::saveConfig or something */
            $this->title = '!!! CONFIG ERROR !!!';
          }
        }
      }
    }
    else {
      $this->user = $this->User->create();
      $this->user->username = 'root';
    }

    $this->render();
  }

}
