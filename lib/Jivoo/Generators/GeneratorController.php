<?php
/**
 * Controller for generators
 * @package Jivoo\Generators
 */
class GeneratorController extends Controller {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Generators');
  
  /**
   * Application generation frontpage.
   * @return ViewResponse Response.
   */
  public function index() {
    $this->title = tr('Generate application');
    $this->appDir = $this->p('app');
    return $this->render();
  }
}
