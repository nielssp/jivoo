<?php
/**
 * Generators module for generating Jivoo applications.
 * @package Jivoo\Generators
 */
class Generators extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Controllers', 'Routing', 'View');

  /**
   * {@inheritdoc}
   */
  protected function init() {
  }
  
  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if (empty($this->app->appConfig)) {
      $controller = $this->m->Controllers->getController('Generator');
      $controller->autoRoute('index');
      $this->m->Routing->setRoot('Generator::index');
      $this->view->addTemplateDir($this->p('templates'));
    }
  }
}
