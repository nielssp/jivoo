<?php
abstract class Module {
  // supports events -[and behaviours]
  // no more filemeta on every load?

  protected $modules = array();

  protected $app = null;
  protected $m;

  /**
   * @var AppConfig Module configuration
   */
  protected $config = null;

  /**
   * @var Request|null The Request object if available
   */
  protected $request = null;

  /**
   * @var SessionStorage|null Session storage object if available
   */
  protected $session = null;

  /**
   * @var View|null Current view if available
   */
  protected $view = null;

  private $e;

  public function __construct(App $app) {
    $this->app = $app;
    $this->config = $app->config;
    $this->m = $app->getModules($this->modules);
    if (isset($this->m->Routing)) {
      $this->request = $this->m->Routing->request;
      $this->session = $this->request->session;
    }
    if (isset($this->m->Templates))
      $this->view = $this->m->Templates->view;

    $this->e = new EventManager($this);
  }

  /**
   * Get the absolute path of a file.
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path) {
    return $this->app->p($key, $path);
  }

  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->app->w($path);
  }

  public function addEventHandler($event, $callback) {
    $this->e->attach($event, $callback);
  }

  public function addEventListener(IEventListener $listener) {
    foreach ($listener->getEvents() as $event => $method) {
      $this->e->$event[] = array($listener, $method);
    }
  }

  protected function trigger($event, $eventArgs = null) {
    $this->e->trigger($event, $eventArgs);
  }
}