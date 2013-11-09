<!DOCTYPE html>
<html>
<head>





<style type="text/css">
* {
  padding:0;
  margin:0;
}

body {
  font-family:'Trebuchet MS', sans-serif;
}

.tk-container {
  padding:10px;
}

.tk-field-set {
  background-color:#fff;
  border:1px solid #ccc;
  border-radius:5px;
  margin:5px;
  padding:10px;
}

.tk-form {
  background-color:#eee;
  border:1px solid #ccc;
  border-top-right-radius:10px;
  border-top-left-radius:10px;
  padding:10px;
}

.tk-form-buttons {
  background-color:#ccc;
  border:1px solid #ccc;
  border-bottom-right-radius:10px;
  border-bottom-left-radius:10px;
  padding:10px;
}
  
.tk-form-buttons .tk-button-set{
  text-align:right;
}

.tk-button {
  padding:5px 20px;
}

.tk-text {
  width:100%;
  display:block;
  border:1px solid #ccc;
  border-radius:5px;
  font-size:16px;
}

.tk-row {
  margin-left:-15px;
  margin-right:-15px;
}

.tk-col-1, .tk-col-2, .tk-col-3, .tk-col-4, .tk-col-5, .tk-col-6, .tk-col-7, .tk-col-8, 
.tk-col-9, .tk-col-10, .tk-col-11, .tk-col-12, .tk-col-13, .tk-col-14, .tk-col-15, .tk-col-16 {
  box-sizing:border-box;
  -moz-box-sizing:border-box; /* Firefox */
  position:relative;
  float:left;
  padding-left:15px;
  padding-right:15px;
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
</head>
<body>

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
    $output = '<div class="tk-container">';
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
    $output = '<div class="tk-row">';
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
    return '<input type="button" class="tk-button" value="' . $this->label . '" />';
  }
  
}

class TkButtonSet extends TkWidget {
  private $buttons = array();
  
  public function append(TkButton $button) {
    $this->buttons[] = $button;
  }
  
  public function render() {
    $output = '<div class="tk-button-set">';
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
    $output = '<form><div class="tk-form">';
    foreach ($this->fields as $field) {
      $output .= $field->render();
    }
    $output .= '</div>';
    $output .= '<div class="tk-form-buttons">' . $this->buttonSet->render();
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
    $output = '<div class="tk-field"><label class="tk-field-label" for="">' . $this->label . ':</label>';
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
    $output = '<fieldset class="tk-field-set"><legend class="tk-field-legend">' . $this->label . ':</legend>';
    foreach ($this->fields as $field) {
      $output .= $field->render();
    }
    return $output . '</fieldset>';
  }
}

class TkMultiField extends TkField {

  private $label;
  private $distribution = array();
  private $fields = array();
  
  public function __construct($label, $distribution) {
    $this->label = $label;
    $this->distribution = $distribution;
  }
  public function append(TkField $field) {
    $this->fields[] = $field;
  }
  
  public function render() {
    $output = '<div class="tk-multi-field"><label class="tk-multi-field-label" for="">' . $this->label . ':</label><br/>';
    $colCounter = 0;
    $colCount = count($this->distribution);
    foreach ($this->fields as $field) {
      $width = $this->distribution[$colCounter];
      $output .= '<div class="tk-col-' . $width . '">' . $field->render() . '</div>';
      $colCounter = ($colCounter + 1) % $colCount;
    }
    return $output . '</div>';
  }
}

class TkTextField extends TkField {
  public function __construct($label) {
    parent::__construct($label, new TkText());
  }
}

class TkText extends TkWidget {
  public function render() {
    return '<input type="text" class="tk-text" />';
  }
}

class TkLabel extends TkWidget {
  public $text = '';
  public function __construct($text) {
    $this->text = $text;
  }
  public function render() {
    return '<label class="tk-label">' . $this->text . '</label>';
  }
}


$root = new TkContainer();

$form = new TkForm();

$fieldset = new TkFieldSet('Identity');

$multiField = new TkMultiField('Name', array(6, 4, 6));
$multiField->append(new TkTextField('First'));
$field = new TkTextField('Middle');
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
?>

</body>
