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
   * Construct view respons.
   * @param int $status Status code.
   * @param View $view The view.
   * @param string $template Template name.
   */
  public function __construct($status, View $view, $template) {
    parent::__construct($status, Utilities::getContentType($template));
    $this->view = $view;
    $this->template = $template;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->view->render($this->template);
  }
}
