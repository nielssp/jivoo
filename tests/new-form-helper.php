<?php
ini_set('display_errors', true);

require '../lib/Core/bootstrap.php';

Lib::import('Core');

$app = new App(require '../app/app.php', basename(__FILE__));

$routing = $app->loadModule('Core/Routing');
$helpers = $app->loadModule('Core/Helpers');

class FormHelper extends Helper {

  public function form($route = array(), $options = array()) {
    $options = array_merge(array(
      'method' => 'post',
    ), $options);
    $html = '<form action="' . $this->getLink($route) . '"';
    $html .= ' method="' . $options['method'] . '">';
    return $html . PHP_EOL;
  }

  public function formFor($record, $route = array(), $options = array()) {
  }

  public function end() {
    return '</form>' . PHP_EOL;
  }

  public function label($field, $label) {
    $html = '<label for="' . $field . '">' . $label . '</label>';
    return $html . PHP_EOL;
  }

  public function text($field) {
    $html = '<input type="text" name="' . $field . '" id="' . $field . '" />';
    return $html . PHP_EOL;
  }

  public function submit($label) {
    $html = '<input type="submit" value="' . $label . '" />';
    return $html . PHP_EOL;
  }

  /**
   * Add additional attributes
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  private function addAttributes($options) {
    $html = '';
    foreach ($options as $attribute => $value) {
      $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }
}

header('Content-Type: text/plain');

$routing->addRoute('GET search', 'App::search');

$Form = new FormHelper($routing);

?>


<?php echo $Form->form('App::search', array('method' => 'get')); ?>
  <?php echo $Form->label('q', 'Search for'); ?>
  <?php echo $Form->text('q'); ?>
  <?php echo $Form->submit('Search'); ?>
<?php echo $Form->end(); ?>
