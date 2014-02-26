<?php
ini_set('display_errors', true);

require '../lib/Core/bootstrap.php';

Lib::import('Core');

$app = new App(require '../app/app.php', basename(__FILE__));

$routing = $app->loadModule('Core/Routing');
$helpers = $app->loadModule('Core/Helpers');

class FormHelperException extends Exception { }

/**
 * A helper for creating HTML forms
 * @package Core\Helpers
 */
class FormHelper extends Helper {
  
  /**
   * @var string[]
   */
  private $stack = array();

  /**
   * @var IBasicRecord Associated record
   */
  private $record = null;
  
  /**
   * @var IBasicModel Associated model
   */
  private $model = null;
  
  /**
   * @var string Name of current form
   */
  private $name = null;
  
  /**
   * @var string Id of current form
   */
  private $id = null;
  
  /**
   * @var array Associative array of field names and error messages
   */
  private $errors = array();
  
  /**
   * @var unknown
   */
  private $selectValue = null;

  public function form($route = array(), $attributes = array()) {
    if (!empty($this->stack))
      throw new FormHelperException(tr('A form is already open.'));
    array_push($this->stack, 'form');
    $attributes = array_merge(array(
      'method' => 'post',
    ), $attributes);
    $specialMethod = null;
    if ($attributes['method'] != 'post' and
        $attributes['method'] != 'get') {
      $specialMethod = $attributes['method'];
      $attributes['method'] = 'post';
    }
    if (isset($attributes['id']))
      $this->id = $attributes['id'];
    if (isset($attributes['name']))
      $this->name = $attributes['name'];
    $html = '<form action="' . $this->getLink($route) . '"';
    $html .= $this->addAttributes($attributes) . '>' . PHP_EOL;
    $html .= $this->request->createHiddenToken() . PHP_EOL;
    if (isset($specialMethod)) {
      $html .= $this->element('input', array(
        'type' => 'hidden',
        'name' => 'method',
        'value' => $specialMethod
      ));
    }
    return $html;
  }

  public function formFor(IBasicRecord $record, $route = array(), $attributes = array()) {
    $this->record = $record;
    $this->model = $record->getModel();
    $attributes = array_merge(array(
      'id' => $this->model->getName(),
      'name' => $this->model->getName(),
    ), $attributes);
    return $this->form($route, $attributes);
  }

  public function end() {
    if (empty($this->stack))
      throw new FormHelperException(tr('No form or form element is open.'));
    $element = array_pop($this->stack);
    switch ($element) {
      case 'form':
        $this->errors = array();
        $this->record = null;
        $this->model = null;
        $this->name = null;
        $this->id = null;
        return '</form>' . PHP_EOL;
      case 'select':
        $this->selectValue = null;
        return '</select>' . PHP_EOL;
      case 'optgroup':
        return '</optgroup>' . PHP_EOL;
    }
  }
  
  public function id($field, $value = null) {
    if (isset($this->id))
      $field = $this->id . '_' . $field;
    if (isset($value))
      $field .= '_' . $value;
    return $field;
  }
  
  public function name($field) {
    if (isset($this->name))
      return $this->name . '[' . $field . ']';
    return $field;
  }
  
  public function value($field) {
    if (isset($this->record)) {
      return $this->record->$field;
    }
    return null;
  }

  public function label($field, $label, $attributes = array()) {
    $attributes = array_merge(array(
      'for' => $this->id($field)
    ), $attributes);
    return $this->element('label', $attributes, $label);
  }

  public function radioLabel($field, $value, $label, $attributes = array()) {
    $attributes = array_merge(array(
      'for' => $this->id($field, $value)
    ), $attributes);
    return $this->element('label', $attributes, $label);
  }

  public function text($field, $attributes = array()) {
    return $this->inputElement('text', $field, $attributes);
  }

  public function password($field, $attributes = array()) {
    return $this->inputElement('password', $field, $attributes);
  }

  public function hidden($field, $attributes = array()) {
    return $this->inputElement('hidden', $field, $attributes);
  }
  
  public function radio($field, $value, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'radio'
    ), $attributes);
    return $this->checkbox($field, $value, $attributes);
  }
  
  public function checkbox($field, $value, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'checkbox',
      'name' => $this->name($field),
      'id' => $this->id($field, $value)
    ), $attributes);
    $attributes['value'] = $value;
    $currentValue = $this->value($field);
    if ($currentValue == $value) {
      $attributes['checked'] = 'checked';
    }
    return $this->element('input', $attributes);
  }
  
  public function select($field, $values = array(), $attributes = array()) {
    if (end($this->stack) != 'form')
      throw new FormHelperException('Must be in a form before using optgroup.');
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $value = $attributes['value'];
    unset($attributes['value']);
    $html = '<select' . $this->addAttributes($attributes) . '>' . PHP_EOL;
    array_push($this->stack, 'select');
    $this->selectValue = $value;
    return $html;
  }
  
  public function optgroup($label, $attributes = array()) {
    if (end($this->stack) != 'select')
      throw new FormHelperException('Must be in a select-field before using optgroup.');
    $attributes['label'] = $label;
    array_push($this->stack, 'optgroup');
    return '<optgroup' . $this->addAttributes($attributes) . '>' . PHP_EOL;
  }
  
  public function selectOf($field, $values = array(), $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $currentValue = $attributes['value'];
    unset($attributes['value']);
    $html = '<select' . $this->addAttributes($attributes) . '>';
    foreach ($values as $value => $text) {
      $html .= '<option value="' . h($value) . '"';
      if ($currentValue == $value)
        $html .= ' selected="selected"';
      $html .= '>' . h($text) . '</option>' . PHP_EOL;
    }
    return $html;
  }
  
  public function option($value, $text, $attributes = array()) {
    $attributes['value'] = $value;
    if ($value == $this->selectValue)
      $attributes['selected'] = 'selected';
    return $this->element('option', $attributes, $text);
  }

  public function textarea($field, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field)
    ), $attributes);
    $content = '';
    if (isset($attributes['value'])) {
      $content = $attributes['value'];
      unset($attributes['value']);
    }
    if (isset($attributes['size'])) {
      $size = explode('x', $attributes['size']);
      unset($attributes['size']);
      if (count($size) == 2) {
        $attributes['cols'] = $size[0]; 
        $attributes['rows'] = $size[1];
      }
    }
    return $this->element('textarea', $attributes, $content);
  }

  public function submit($label, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'submit',
      'value' => $label
    ), $attributes);
    return $this->element('input', $attributes);
  }
  
  private function inputElement($type, $field, $attributes) {
    $attributes = array_merge(array(
      'type' => $type,
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field)
    ), $attributes);
    return $this->element('input', $attributes);
  }
  
  private function element($tag, $attributes, $content = null) {
    if (isset($content))
      return '<' . $tag . $this->addAttributes($attributes) . '>' . $content . '</' . $tag . '>' . PHP_EOL;
    return '<' . $tag . $this->addAttributes($attributes) . ' />' . PHP_EOL;
  }

  /**
   * Add additional attributes
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  private function addAttributes($attributes) {
    $html = '';
    foreach ($attributes as $attribute => $value) {
      if (is_scalar($value))
        $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }
}

header('Content-Type: text/plain');

$routing->addRoute('GET search', 'App::search');

$Form = new FormHelper($routing);

?>


<?php echo $Form->form('App::search', array('method' => 'get', 'id' => 'search')); ?>
  <?php echo $Form->label('q', 'Search for'); ?>
  <?php echo $Form->text('q'); ?>
  <?php echo $Form->radioLabel('what', 'yes', 'Yes'); ?>
  <?php echo $Form->radio('what', 'yes'); ?>
  <?php echo $Form->radio('what', 'no'); ?>
  <?php echo $Form->select('s'); ?>
    <?php echo $Form->optgroup('My group')?>
      <?php echo $Form->option('val', 'val')?>
    <?php echo $Form->end(); ?>
  <?php echo $Form->end(); ?>
  
  <?php echo $Form->submit(tr('Search')); ?>
<?php echo $Form->end(); ?>
