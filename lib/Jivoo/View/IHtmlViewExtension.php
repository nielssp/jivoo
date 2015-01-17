<?php
/**
 * A view extension that produces HTML.
 * @package Jivoo\View
 */
interface IHtmlViewExtension extends IViewExtension {
  /**
   * Output extension.
   * @return string HTML.
   */
  public function html();
}