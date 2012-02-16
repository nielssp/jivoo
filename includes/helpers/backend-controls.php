<?php
/** 
 * Contains classes used for rendering form controls on backend pages
 *
 * @package PeanutCMS
 */

/**
 * A regular text input
 */
class BackendTextInput {
  
  var $id;
  var $name;
  var $label;
  var $value;
  var $description;
  
  function BackendTextInput() {
    $args = func_get_args();
    return call_user_func_array(array($this, '__construct'), $args);
  }
  
  function __construct($id, $name, $label, $value = '', $description = '') {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
  }
  
  function setValue() {
    if (isset($_POST[$this->name]))
      $this->value = htmlentities($_POST[$this->name], ENT_QUOTES, 'UTF-8');
  }
  
  function renderLabel() {
    return '<div><label for="' . $this->id . '">' . $this->label . '</label></div>';
  }
    
  function renderElement() {
    $input = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"
  value="' . $this->value . '" class="text ui-widget-content ui-corner-all" ';
    if (empty($this->name))
      $input .= 'disabled="disabled" ';
    $input .= '/>';
    return $input;
  }
  
  function renderDescription() {
    if (!empty($this->description))
      return ' <span class="backend-description">' . $this->description . '</span>';
    return '';
  }
  
  function render() {
    $this->setValue();
    $content = '<div class="backend-input">';
    $content .= $this->renderLabel();
    $content .= $this->renderElement();
    $content .= $this->renderDescription();
    $content .= '</div>
';
    return $content;
  }
}

class BackendPasswordInput extends BackendTextInput {
  var $hint;
  
  function __construct($id, $name, $label, $value = '', $description = '', $hint = false) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->hint = $hint;
  }
  
  function renderElement() {
    $input = '<script type="text/javascript">
$(function() {
  $("#' . $this->id . '").keyup(function() {
    var bar = $(this).parent().find(".backend-strength-bar");
    var string = $(this).parent().find(".backend-strength-string");
    var hint = "";
    var password = $(this).val();
    if ($(this).val().length < 6) {
      hint = "' . tr('Minimum of 6 characters in length') . '";
      bar.width("0%");
      string.html("' . tr('Too short') . '");
      string.css("color", "#999");
    }
    else {
      var strength = 0;
      if (password.match(/[a-z]/))
        strength++;
      else if (hint == "")
        hint = "' . tr('Hint: Add lowercase characters') . '";
      if (password.match(/[A-Z]/))
        strength++;
      else if (hint == "")
        hint = "' . tr('Hint: Add uppercase characters') . '";
      if (password.match(/[0-9]/))
        strength++;
      else if (hint == "")
        hint = "' . tr('Hint: Add numbers') . '";
      if (password.match(/[^a-z0-9]/i))
        strength++;
      else if (hint == "")
        hint = "' . tr('Hint: Add punctuation') . '";
      if (strength == 1) {
        bar.width("25%");
        bar.css("backgroundColor", "#900");
        string.html("' . tr('Weak') . '");
        string.css("color", "#900");
      }
      if (strength == 2) {
        bar.width("50%");
        bar.css("backgroundColor", "#f90");
        string.html("' . tr('Fair') . '");
        string.css("color", "#f90");
      }
      if (strength == 3) {
        bar.width("75%");
        bar.css("backgroundColor", "#090");
        string.html("' . tr('Strong') . '");
        string.css("color", "#090");
      }
      if (strength == 4) {
        bar.width("100%");
        bar.css("backgroundColor", "#009");
        string.html("' . tr('Very strong') . '");
        string.css("color", "#009");
      }
    }
    $(this).parent().find(".backend-strength-hint").html(hint);
  });
});
</script>
';
    $input .= '<div>';
    if ($this->hint) {
      $input .= '<div class="backend-strength-indicator-wrapper">
<div class="backend-strength-indicator">
<div class="backend-strength-string">' . tr('Too short') . '</div>
<strong>' . tr('Password strength') . '</strong>
<div class="backend-strength-bar-wrapper">
<div class="backend-strength-bar"></div>
</div>
<span class="backend-strength-hint">' . tr('Minimum of 6 characters in length') . '</span>
</div>
</div>
';
    }
    $input .= '<input type="password" name="' . $this->name . '" id="' . $this->id . '"
  class="text ui-widget-content ui-corner-all';
    if ($this->hint)
      $input .= ' backend-password';
    $input .= '" ';
    if (empty($this->name))
      $input .= 'disabled="disabled" ';
    $input .= '/>';
    $input .= '</div>';
    return $input;
  }
}

class BackendFormatSelect extends BackendTextInput {
  
  var $formats;
  var $formatter;
  var $custom;
  
  function __construct($id, $name, $label, $value = '', $description = '', $formats = array(), $formatter = null) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->formats = $formats;
    $this->formatter = $formatter;
  }

  function setValue() {
    if (isset($_POST[$this->name]))
      $this->value = $_POST[$this->name];
    if (isset($_POST[$this->name . '_custom']))
      $this->custom = htmlentities($_POST[$this->name . '_custom'], ENT_QUOTES, 'UTF-8');
    else {
      $this->custom = $this->value;
    }
  }
  
  function renderElement() {
    global $PEANUT;
    $content = '<div class="backend-format-radioset">';
    $checked = false;
    $i = 0;
    foreach ($this->formats as $label => $formatstring) {
      $content .= '<label class="backend-radio" title="' . $formatstring . '">
<input type="radio" value="' . $formatstring . '" name="' . $this->name . '" id="' . $this->id . $i . '"';
      if ($this->value == $formatstring) {
        $content .= ' checked="checked"';
        $checked = true;
      }
      $content .= '> ';
      if (is_string($label))
        $content .= '<strong>' . $label . '</strong> ';
      if (is_callable($this->formatter))
        $content .= call_user_func($this->formatter, $formatstring) . '</label><br/>';
      else
        $content .= $formatstring . '</label><br/>';
      $i++;
    }
    $content .= '<label class="backend-radio-custom"><input type="radio" value="custom" name="' . $this->name . '" id="' . $this->id . $i . '"';
    if ($checked == false)
      $content .= ' checked="checked"';
    $content .= '> <strong>' . tr('Custom format') . '</strong> </label> <input type="text"
      name="' . $this->name . '_custom" id="' . $this->id . '_custom"
        class="text-inline ui-widget-content ui-corner-all" value="' . $this->custom . '" /> ';
    if ($checked == false) {
      if (is_callable($this->formatter))
        $content .= call_user_func($this->formatter, $this->custom);
      else
        $content .= $this->custom;
    }
//    $content .= ' <a href="http://php.net/manual/en/function.date.php">(?)</a>';
    $content .= '</div>';
    return $content;
  }
}


class BackendLanguageSelect extends BackendTextInput {
  function renderElement() {
    global $PEANUT;
    $languages = $PEANUT['i18n']->listLanguages();
    $content = '<div><select class="ui-widget-content ui-corner-all" id="' . $this->id . '"
      name="' . $this->name . '">';
    foreach ($languages as $languageId => $language) {
      $content .= '<option value="' . $languageId . '"';
      if ($languageId == $this->value)
        $content .= ' selected="selected"';
      $content .= '>' . $language . '</option>';
    }
    $content .= '</select></div>';
    return $content;
  }
}

class BackendNumericSelect extends BackendTextInput {
  
  var $min;
  var $max;
  
  function __construct($id, $name, $label, $value = '', $description = '', $min = 0, $max = 10) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->min = $min;
    $this->max = $max;
  }
  
  function renderElement() {
    $content = '<div><select class="ui-widget-content ui-corner-all" id="' . $this->id . '"
      name="' . $this->name . '">';
    for ($i = $this->min; $i <= $this->max; $i++) {
      $content .= '<option value="' . $i . '"';
      if ($this->value == $i)
        $content .= ' selected="selected"';
      $content .= '>' . $i . '</option>';
    }
    $content .= '</select></div>';
    return $content;
  }
}

class BackendThemeSelect extends BackendTextInput {
  function renderElement() {
    global $PEANUT;
    $dir = opendir(PATH . INC . 'css');
    if (!$dir)
      return false;
    $themes = array();
    while (($theme = readdir($dir)) !== false) {
      if (is_dir(PATH . INC . 'css/' . $theme) AND $theme != '.' AND $tteme != '..') {
        $themeext = explode('.', $theme);
        if (!empty($themeext[0]) AND is_file(PATH . INC . 'css/' . $theme . '/jquery-ui-1.8.16.custom.css'))
          $themes[] = $themeext[0];
      }
    }
    closedir($dir);
    $content = '<div><select class="ui-widget-content ui-corner-all" id="' . $this->id . '"
      name="' . $this->name . '">';
    foreach ($themes as $theme) {
      $content .= '<option value="' . $theme . '"';
      if ($theme == $this->value)
        $content .= ' selected="selected"';
      $content .= '>' . $theme . '</option>';
    }
    $content .= '</select></div>';
    return $content;
  }
}

class BackendTimeZoneSelect extends BackendTextInput {
  function setValue() {
    if (isset($_POST[$this->name]))
      $this->value = htmlentities($_POST[$this->name], ENT_QUOTES, 'UTF-8');
    if (!defined('DATETIMEZONE_AVAILABLE')) {
      $this->description = '<strong>' . tr('Current UTC time is %1.', date('H:i', time() - TIMEZONE_OFFSET)) . '</strong> ';
      $this->description .= tr('You are using an older version of PHP, so you will manually have to update the time zone for daylight saving time.');
      if (!is_numeric($this->value))
        $this->value = TIMEZONE_OFFSET;
    }
  }
  
  function renderElement() {
    $content = '<div><select class="ui-widget-content ui-corner-all" id="' . $this->id . '"
      name="' . $this->name . '">';
    if (defined('DATETIMEZONE_AVAILABLE')) {
      $content .= '<option value="UTC">UTC</option>';
      $timezones = DateTimeZone::listIdentifiers();
      $regiongroup = '';
      foreach ($timezones as $timezoneitem) {
        $timezoneexp = explode('/', $timezoneitem);
        if ($timezoneexp[0] != 'UTC') {
          $region = array_shift($timezoneexp);
          if ($region != $regiongroup) {
            if ($regiongroup != '')
              $content .= '</optgroup>';
            $content .= '<optgroup label="' . $region . '">';
            $regiongroup = $region;
          }
          $content .= '<option value="' . $timezoneitem . '"';
          if ($timezoneitem == $this->value)
            $content .= ' selected="selected"';
          $content .= '>' . str_replace('_', ' ', implode(' - ', $timezoneexp)) . '</option>';
        }
      }
      if ($regiongroup != '')
        $content .= '</optgroup>';

    }
    else {
      $defaultO = '+';
      $defaultH = date('H', 0);
      $defaultM = date('i', 0);
      if (date('Y', 0) == '1969') {
        $defaultH = 24-$defaultH;
        $defaultO = '-';
      }
      if ($defaultM != 0) {
        $defaultM = 30;
        if ($defaultO == '-')
          $defaultH = $defaultH - 1;
      }
      $default = $defaultO . sprintf('%02d', $defaultH) . ':' . sprintf('%02d', $defaultM);
      for ($i = 0; $i < 53; $i++) {
        $offset = $i/2-12;
        $offset_seconds = $offset*60*60;
        if ($offset > 0) {
          if (is_int($offset))
            $offset = '+' . sprintf('%02d', $offset) . ':00';
          else
            $offset = '+' . sprintf('%02d', $offset-0.5) . ':30';
        }
        else if ($offset < 0) {
          if (is_int($offset))
            $offset = '-' . sprintf('%02d', $offset*(-1)) . ':00';
          else
            $offset = '-' . sprintf('%02d', $offset*(-1)-0.5) . ':30';
        }
        else {
          $offset = '';
        }
        $content .= '<option value="' . $offset_seconds . '"';
        if ($offset_seconds == $this->value)
          $content .= ' selected="selected"';
        $content .= '>UTC';
        $content .= $offset;
        $content .= '</option>';
      }
    }
    $content .= '</select></div>';
    return $content;
  }
}

class BackendRadioInput extends BackendTextInput {
  var $radios;
  
  function __construct($id, $name, $label, $value = '', $description = '', $radios = array()) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->radios = $radios;
  }
  
  function renderElement() {
    $input = '<div class="backend-radioset">';
    $i = 0;
    foreach ($this->radios as $value => $label) {
      $input .= '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="' . $value . '" ';
      if ($value == $this->value)
        $input .= 'checked="checked" ';
      $input .= '/><label for="' . $this->id . $i . '">' . $label . '</label> ';
      $i++;
    }
    $input .= '</div>';
    return $input;
  }
}

class BackendDateInput extends BackendTextInput {
  function setValue() {
    if (isset($_POST[$this->name]))
      $this->value = htmlentities($_POST[$this->name], ENT_QUOTES, 'UTF-8');
    else if (empty($this->value))
      $this->value = date('m/d/Y');
  }
  
  function renderElement() {
    return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"
  value="' . $this->value . '" class="text ui-widget-content ui-corner-all backend-datepicker" />';
  }
}

class BackendTinyMce extends BackendTextInput {
  function renderElement() {
    return '<textarea name="' . $this->name . '" id="' . $this->id . '"
  class="ui-widget-content ui-corner-all backend-wysiwyg">' . $this->value . '</textarea>';
  }
}

class BackendPermalinkInput extends BackendTextInput {
  var $function;
  var $for;
  var $allowSlash;
  
  function __construct($id, $name, $label, $value = '', $description = '', $function = null, $for = null, $allowSlash = false) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->function = $function;
    $this->for = $for;
    $this->allowSlash = $allowSlash;
  }
  
  function renderElement() {
    $input = '<input type="text" id="' . $this->id . '" name="' . $this->name . '"';
    if (!empty($this->for))
      $input .= ' rev="' . $this->for . '"';
    $input .= ' class="text-inline ui-widget-content ui-corner-all backend-permalink';
    if ($this->allowSlash == true)
      $input .= ' backend-permalink-allow-slash';
    $input .= '" value="' . $this->value . '" />';
    if (is_callable($this->function))
      $input = '<div class="ui-widget-content ui-corner-all backend-permalink-wrapper">' . call_user_func($this->function, $input) . '</div>';
    else if (is_string($this->function))
      $input = '<div class="ui-widget-content ui-corner-all backend-permalink-wrapper">' . $this->function . $input . '</div>';
    else
      $input = '<span>' . $input . '</span>';
    $input = '<div>' . $input;
    if (!empty($this->for))
      $input .= ' <a href="#" class="backend-permalink-unlock">Unlock</a>';
    return $input . '</div>';
  }
}

class BackendAutoComplete extends BackendTextInput {
  var $values;
  var $multiple;
  
  function __construct($id, $name, $label, $value = '', $description = '', $values = array(), $multiple = false) {
    $this->id = $id;
    $this->name = $name;
    $this->label = $label;
    $this->value = $value;
    $this->description = $description;
    $this->values = $values;
    $this->multiple = $multiple;
  }

  function renderElement() {
    $script = '<script type="text/javascript">
$(function() {
  var availableValues = ["' . implode('", "', $this->values) . '"];
';
    if ($this->multiple) {
      $script .= '
  $("#' . $this->id . '")
    .bind( "keydown", function( event ) {
      if ( event.keyCode === $.ui.keyCode.TAB &&
          $( this ).data( "autocomplete" ).menu.active ) {
        event.preventDefault();
      }
    })
    .autocomplete({
      minLength: 0,
      source: function( request, response ) {
        // delegate back to autocomplete, but extract the last term
        response( $.ui.autocomplete.filter(
          availableValues, request.term.split( /,\s*/ ).pop() ) );
      },
      focus: function() {
        // prevent value inserted on focus
        return false;
      },
      select: function( event, ui ) {
        var terms = this.value.split( /,\s*/ );
        // remove the current input
        terms.pop();
        // add the selected item
        terms.push( ui.item.value );
        // add placeholder to get the comma-and-space at the end
        terms.push( "" );
        this.value = terms.join( ", " );
        return false;
      }
    });';
    }
    else {
      $script .= '
  $("#' . $this->id . '").autocomplete({
    source: availableValues
  });';
    }
    $script .= '
});
</script>';
    
    return $script . '<input type="text" name="' . $this->name . '" id="' . $this->id . '"
  value="' . $this->value . '" class="text ui-widget-content ui-corner-all" />';
  }
}

class BackendDataTable {
  
  var $table;
  var $index;
  var $columns;
  var $actions;
  var $rows;
  
  var $start;
  var $end;

  function BackendDataTable() {
    $args = func_get_args();
    return call_user_func_array(array($this, '__construct'), $args);
  }
  
  function __construct($table, $index, $columns, $actions) {
    $this->table = $table;
    $this->index = $index;
    $this->columns = $columns;
    $this->actions = $actions;
    
    $this->start = 0;
    $this->end = 10;
  }
  
  function getRows() {
    global $PEANUT;
    $index = $PEANUT['flatfiles']->getIndex($this->table, $this->index);
    asort($index);
    reset($index);
    $i = 0;
    $this->rows = array();
    foreach ($index as $rowId => $date) {
      if ($i >= $this->start AND $i < $this->end) {
        $this->rows[] = $PEANUT['flatfiles']->getRow($this->table, $rowId);
      }
      $i++;
    }
  }
  
  function render() {
    global $PEANUT;
    $this->getRows();
    $content = '<table class="backend-data-table" cellspacing="0" cellpadding="0"><thead><tr>';
    $ih = 0;
    foreach ($this->columns as $column => $columnData) {
      $content .= '<th class="';
      if ($ih == 0)
        $content .= ' ui-corner-tl';
      else
        $content .= ' backend-th';
      $content .= '" style="';
      if (isset($columnData['width']))
        $content .= 'width:' . $columnData['width'] . 'px;';
      $content .= '">' . $columnData['label'] . '</th>';
      $ih++;
    }
    $content .= '<th class="ui-corner-tr" style="width:' . count($this->actions)*75 . 'px;">' . tr('Actions') . '</th></tr></thead><tbody>';
    $ir = 0;
    foreach ($this->rows as $row) {
      $content .= '<tr>';
      $ib = 0;
      foreach ($this->columns as $column => $columnData) {
        $content .= '<td class="';
        if ($ib == 0)
          $content .= ' backend-td-left';
        else
          $content .= ' backend-td';
        if ($columnData['type'] == 'date')
          $row[$column] = $PEANUT['i18n']->date($PEANUT['i18n']->dateFormat(), $row[$column]);
        $content .= '">' . $row[$column] . '</th>';
        $ib++;
      }
      $content .= '<td class="';
      $content .= ' backend-td-right">';
      foreach ($this->actions as $label => $action) {
        foreach ($action as $key => $value)
          $action[$key] = str_replace('%id%', $row['id'], $value);
        $link = $PEANUT['http']->getLink(null, $action);
        $content .= '<a href="' . $link . '" class="backend-button">' . $label . '</a> ';
      }
      $content .= '</td></tr>';
      $ir++;
    }
    $content .= '</tbody><tfoot><tr><td class="ui-state-default ui-corner-bottom" style="border-top:0;" colspan="' . ($ih + 1) . '"></td></tr></table>';
    return $content;
  }
}

class BackendPageTypes {
  function BackendPageTypes() {
    $args = func_get_args();
    return call_user_func_array(array($this, '__construct'), $args);
  }
  
  function __construct() {
   
  }
  
  function render() {
    global $PEANUT;

    $content = '<table class="backend-data-table" cellspacing="0" cellpadding="0"><thead><tr>';
    $content .= '<th class="ui-corner-tl">' . tr('Label') . '</th>';
    $content .= '<th class="backend-th" style="width:100px;">' . tr('Type') . '</th>';
    $content .= '<th class="ui-corner-tr" style="width:175px;">' . tr('Actions') . '</th></tr></thead><tbody class="backend-sortable">';
    $ir = 0;
    foreach ($PEANUT['configuration']->get('menu') as $menuItem) {
      $content .= '<tr>';
      $content .= '<td class="backend-td-left">' . $menuItem['label'] . '</td>';
      $content .= '<td class="backend-td">' . $menuItem['template'] . '</td>';
      $content .= '<td class="backend-td-right"></td>';
      $content .= '</tr>';
      $ir++;
    }
    $content .= '</tbody><tfoot><tr><td class="ui-state-default ui-corner-bottom" style="border-top:0;" colspan="3"></td></tr></table>';
    return $content;
  }
}