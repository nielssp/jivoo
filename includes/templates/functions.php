<?php
/**
 * Default theming functions and filters
 *
 * If you create a theme you can overwrite these functions in your functions.php-file
 *
 * @package PeanutCMS
 */

/**
 * Function for outputting different form input controls
 */
function defaultFormInput($type, $name, $label = '', $value = '', $description = '', $mandatory = 'optional', $error = null) {
  if ($type == 'hidden')
    return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '"  />';
  $output = '<p>';
  if ($type != 'submit' AND $type != 'button' AND !empty($label)) {
    $output .= '<label for="' . $name . '">' . $label . '</label> ';
    if ($mandatory == 'mandatory')
      $output .= '<span class="star">*</span>';
  }
  switch ($type) {
    case 'textarea':
      $output .= '<textarea name="' . $name . '" id="' .  $name . '">' . $value . '</textarea>';
      break;
    case 'submit':
    case 'button':
      $output .= '<input type="' . $type . '" class="button" name="' . $name . '" id="' . $name . '" value="' . $label . '" />';
      break;
    case 'password':
    case 'text':
    default:
      $output .= '<input type="' . $type . '" class="text" name="' . $name . '" id="' . $name . '" value="' . $value . '" />';
      break;
  }
  if (!empty($description) AND empty($error))
    $output .= ' ' . $description;
  if (!empty($error))
    $output .= ' ' . $error;
  $output .= '</p>';
  return $output;
}

$PEANUT['functions']->register('formInput', 'defaultFormInput');

function defaultThemeTitle() {
  global $PEANUT;
  $siteTitle = $PEANUT['configuration']->get('title');
  $siteSubtitle = $PEANUT['configuration']->get('subtitle');
  $pageTitle = $PEANUT['templates']->getTitle();
  if (isset($pageTitle))
    return $pageTitle . ' | ' . $siteTitle;
  if (isset($siteSubtitle))
    return $siteTitle . ' | ' . $siteSubtitle;
  return $siteTitle;
}

$PEANUT['functions']->register('themeTitle', 'defaultThemeTitle');

