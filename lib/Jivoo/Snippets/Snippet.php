<?php
/**
 * A loadable snippet.
 * @package Jivoo\Snippets 
 */
class Snippet extends Module implements ISnippet {
  /**
   * @var string[] A list of other helpers needed by this helper.
  */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by this helper.
  */
  protected $models = array();
  
  /**
   * @var string[] Names of parameters required by this snippet.
   */
  protected $parameters = array();
  
  /**
   * @var Helper[] An associative array of helper names and objects.
  */
  private $helperObjects = array();
  
  /**
   * @var IBasicModel[] An associative array of model names and objects.
  */
  private $modelObjects = array();
  
  /**
   * Construct snippet.
   */
  public final function __construct(App $app) {
    $this->inheritElements('modules');
    $this->inheritElements('helpers');
    $this->inheritElements('models');
    parent::__construct($app);
    
    $this->init();
  }
  
  /**
   * Snippet initialization, called by constructor.
   */
  protected function init() { }

  /**
   * Respond to a GET request.
   * @return Response|string A response object or content.
   */
  public function get() {
    return $this->render();
  }

  /**
   * Respond to a POST request.
   * @param array $data POST data.
   * @return Response|string A response object or content.
   */
  public function post($data) {
    return $this->get();
  }

  /**
   * Respond to a PUT request.
   * @param array $data PUT data.
   * @return Response|string A response object or content.
   */
  public function put($data) {
    return $this->get();
  }

  /**
   * Respond to a PATCH request.
   * @param array $data PATCH data.
   * @return Response|string A response object or content.
   */
  public function patch($data) {
    return $this->get();
  }

  /**
   * Respond to a GET request.
   * @return Response|string A response object or content.
   */
  public function delete() {
    return $this->get();
  }
  
  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->request->isGet())
      return $this->get();
    $name = get_class($this);
    if (!$this->request->hasValidData($name))
      return $this->get();
    $data = $this->request->data[$name];
    switch ($this->request->method) {
      case 'POST':
        return $this->post($data);
      case 'PUT':
        return $this->put($data);
      case 'PATCH':
        return $this->patch($data);
      case 'DELETE':
        return $this->delete();
    }
    return $this->invalid();
  }

  /**
   * Redirect to a route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  protected function redirect($route = null) {
    $this->m->Routing->redirect($route);
  }

  /**
   * Refresh the current path with optional query data and fragment.
   * @param array $query Associative array of query data.
   * @param string $fragment Fragment of page.
   */
  protected function refresh($query = null, $fragment = null) {
    $this->m->Routing->refresh($query, $fragment);
  }

  /**
   * Set HTTP status code, e.g. 200 for OK or 404 for file not found.
   * @param integer $httpStatus HTTP status code.
   */
  protected function setStatus($httpStatus) {
    $this->status = $httpStatus;
  }
  
  /**
   * Call when request is invalid.
   * @return Response|string A response object or content.
   */
  protected function invalid() {
    throw new NotFoundException(tr('Invalid request.'));
  }
}