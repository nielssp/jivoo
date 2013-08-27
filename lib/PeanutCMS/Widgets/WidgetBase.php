<?php
/**
 * Widget base class
 * @package PeanutCMS\Widgets
 */
abstract class WidgetBase implements IHelpable {
  
  /**
   * Main widget logic. Is called before rendering page with widget on.
   */
  public abstract function main();
}