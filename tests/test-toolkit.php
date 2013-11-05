<style type="text/css">
.tk-col-1, .tk-col-2, .tk-col-3, .tk-col-4, .tk-col-5, .tk-col-6, .tk-col-7, .tk-col-8, 
.tk-col-9, .tk-col-10, .tk-col-11, .tk-col-12, .tk-col-13, .tk-col-14, .tk-col-15, .tk-col-16 {
  float:left;
}
.tk-col-1  {width:6.25%;}
.tk-col-2  {width:12.5%;}
.tk-col-3  {width:18.75%;}
.tk-col-4  {width:25%;}
.tk-col-5  {width:31.25%;}
.tk-col-6  {width:37.5%;}
.tk-col-7  {width:43.75%;}
.tk-col-8  {width:50%;}
.tk-col-9  {width:56.25%;}
.tk-col-10 {width:62.5%;}
.tk-col-11 {width:68.75%;}
.tk-col-12 {width:75%;}
.tk-col-13 {width:81.25%;}
.tk-col-14 {width:87.5%;}
.tk-col-15 {width:93.75%;}
.tk-col-16 {width:100%;}
</style>

<?php

abstract class TkWidget {
  
  public abstract function render();
}

class TkContainer extends TkWidget {
  
  protected $widgets = array();
  
  public function append(TkWidget $widget) {
    $this->widgets[] = $widget;
  }
  
  public function render() {
    $output = '<div>';
    foreach ($this->widgets as $widget) {
      $output .= $widget->render();
    }
    return $output . '</div>';
  }
}

class TkColumnContainer extends TkContainer {
  private $distribution = array();
  
  public function __construct($distribution = array(16)) {
    $this->distribution = $distribution;
  }
  
  public function render() {
    $output = '<div style="width:100%">';
    $colCounter = 0;
    $colCount = count($this->distribution);
    foreach ($this->widgets as $widget) {
      $width = $this->distribution[$colCounter];
      $output .= '<div class="tk-col-' . $width . '">' . $widget->render() . '</div>';
      $colCounter = ($colCounter + 1) % $colCount;
    }
    return $output . '</div>';
  }
}

class TkButton extends TkWidget {
  private $label;
  public function __construct($label) {
    $this->label = $label;
  }
  
  public function render() {
    return '<input type="button" value="' . $this->label . '" />';
  }
  
}

class TkButtonSet extends TkWidget {
  private $buttons = array();
  
  public function append(TkButton $button) {
    $this->buttons[] = $button;
  }
  
  public function render() {
    $output = '<div style="text-align:right;">';
    foreach ($this->buttons as $button) {
      $output .= $button->render();
    }
    return $output . '</div>';
  }
}

class TkForm extends TkWidget {
  
  private $fields = array();
  
  private $buttonSet;
  
  public function __construct() {
    $this->buttonSet = new TkButtonSet();
  }
  
  public function __get($property) {
    switch ($property) {
      case 'buttonSet':
        return $this->$property;
    }
  }
  
  public function append(TkField $field) {
    $this->fields[] = $field;
  }
  
  public function render() {
    $output = '<form><div>';
    foreach ($this->fields as $field) {
      $output .= $field->render();
    }
    $output .= '</div>';
    $output .= '<div>' . $this->buttonSet->render();
    return $output . '</div></form>';
  }
}

class TkField extends TkWidget {
  private $label;
  private $widget;
  public function __construct($label, TkWidget $widget) {
    $this->label = $label;
    $this->widget = $widget;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'label':
      case 'widget':
        return $this->$property;
    }
  }
  
  public function render() {
    $output = '<div><label for="">' . $this->label . ':</label><br/>';
    $output .= $this->widget->render();
    return $output . '</div>';
  }
}

class TkFieldSet extends TkField {

  private $label;
  private $fields = array();
  
  public function __construct($label) {
    $this->label = $label;
  }
  public function append(TkField $field) {
    $this->fields[] = $field;
  }
  
  public function render() {
    $output = '<fieldset><legend>' . $this->label . ':</legend>';
    foreach ($this->fields as $field) {
      $output .= $field->render();
    }
    return $output . '</fieldset>';
  }
}

class TkMultiField extends TkField {

  private $label;
  private $fields = array();
  
  public function __construct($label) {
    $this->label = $label;
  }
  public function append(TkField $field) {
    $this->fields[] = $field;
  }
  
  public function render() {
    $output = '<div><label for="">' . $this->label . ':</label><br/>';
    foreach ($this->fields as $field) {
      $output .= '<div style="float:left">' . $field->render() . '</div>';
    }
    return $output . '</div><div style="clear:both;"></div>';
  }
}

class TkTextField extends TkField {
  public function __construct($label) {
    parent::__construct($label, new TkText());
  }
}

class TkText extends TkWidget {
  private $size = null;
  
  public function __set($property, $value) {
    switch ($property) {
      case 'size':
        $this->$property = $value;
        break;
    }
  }
  public function render() {
    if (isset($this->size))
      return '<input type="text" size="' . $this->size . '"/>';
    else
      return '<input type="text" />';
  }
}

class TkLabel extends TkWidget {
  public $text = '';
  public function __construct($text) {
    $this->text = $text;
  }
  public function render() {
    return '<label>' . $this->text . '</label>';
  }
}


$root = new TkContainer();

$form = new TkForm();

$fieldset = new TkFieldSet('Identity');

$multiField = new TkMultiField('Name');
$multiField->append(new TkTextField('First'));
$field = new TkTextField('Middle');
$field->widget->size = 2;
$multiField->append($field);
$multiField->append(new TkTextField('Last'));
$fieldset->append($multiField);

$fieldset->append(new TkTextField('Email'));

$form->buttonSet->append(new TkButton('Submit'));

$form->append($fieldset);

$columns = new TkColumnContainer(array(6, 10));
$columns->append($form);
$columns->append($form);
$root->append($columns);

echo $root->render();