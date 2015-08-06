<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

use Jivoo\Core\Utilities;
use Jivoo\Routing\Response;

/**
 * A response that renders a template.
 * @property string $template Template name.
 * @property array $data Addtional data for template.
 * @property bool $withLayout Whether or not to render the layout.
 */
class ViewResponse extends Response {
  /**
   * @var View View.
   */
  private $view;
  
  /**
   * @var string Template name.
   */
  private $template;

  /**
   * @var array
   */
  private $data = array();

  /**
   * @var bool
   */
  private $withLayout = true;

  /**
   * Construct view respons.
   * @param int $status Status code.
   * @param View $view The view.
   * @param string $template Template name.
   * @param array $data Addtional data for template.
   * @param bool $withLayout Whether or not to render the layout.
   */
  public function __construct($status, View $view, $template = null, $data = array(), $withLayout = true) {
    parent::__construct($status, Utilities::getContentType($template));
    $this->view = $view;
    $this->template = $template;
    $this->data = $data;
    $this->withLayout = $withLayout;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'template':
      case 'data':
      case 'withLayout':
        return $this->$property;
    }
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'template':
        $this->type = Utilities::getContentType($value);
      case 'data':
      case 'withLayout':
        $this->$property = $value;
        return;
    }
    parent::__set($property, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    $obLevel = ob_get_level();
    $body = $this->view->render($this->template, $this->data, $this->withLayout);
    if (ob_get_level() > $obLevel) {
      throw new InvalidTemplateException(tr('Ouput buffer not closed in template: %1.', $this->template));
    }
    return $body;
  }
}
