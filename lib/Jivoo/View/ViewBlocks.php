<?php
class ViewBlocks {
  /**
   * @var array Associative array of block names and content
   */
  private $blocks = array();
  
  private $view;
  
  public function __construct(View $view) {
    $this->view = $view;
  }
  
  public function block($block, $default = '') {
    if (isset($this->blocks[$block])) {
      return $this->blocks[$block];
    }
    return $default;
  }
    
  public function isEmpty($block) {
    return !isset($this->blocks[$block]);
  }
  
  public function assign($block, $value) {
    $this->blocks[$block] = $value;
  }
  
  public function append($block, $value) {
    if (!isset($this->blocks[$block]))
      $this->blocks[$block] = '';
    $this->blocks[$block] .= $value;
  }

  public function prepend($block, $value) {
    if (!isset($this->blocks[$block]))
      $this->blocks[$block] = '';
    $this->blocks[$block] = $value . $this->blocks[$block];
  }
  
  /**
   * Insert shortcut icon, will look for file in 'assets/img'
   * @param string $icon Icon name, e.g. 'icon.ico'
   */
  public function icon($icon) {
    $this->relation('shortcut icon', null, $this->view->file('img/' . $icon));
  }
  
  /**
   * Insert meta into view
   * @param string $name Meta name
   * @param string $content Meta content
   */
  public function meta($name, $content) {
    $this->append(
      'meta',
      '<meta name="' . h($name) . '" content="' . h($content) . '" />' . PHP_EOL
    );
  }
  
  /**
   * Insert relation link into view
   * @param string $rel Relationship, e.g. 'stylesheet' or 'alternate'
   * @param string $type Resource type or null for no type
   * @param string $href Resource URL
   * @todo Rename, confuse with ResourceManager resources?
   */
  public function relation($rel, $type, $href) {
    if (isset($type))
      $this->append(
        'meta',
        '<link rel="' . h($rel) . '" type="' . h($type)
         . '" href="' . $href . '" />' . PHP_EOL
      );
    else
      $this->append(
        'meta',
        '<link rel="' . h($rel) . '" href="' . $href . '" />' . PHP_EOL
      );
  }
}