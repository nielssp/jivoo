<?php
ini_set('display_errors', true);
class Localization {
  private $messages = array();
  public function set($message, $translation) {
    $args = func_get_args();
    array_shift($args);
    if (!isset($this->messages[$message]))
      $this->messages[$message] = array();
    $this->messages[$message][] = $args;
  }

  public function get($message) {
    $args = array_slice(func_get_args(), 1);
    if (isset($this->messages[$message])) {
      $patterns = $this->messages[$message];
      foreach ($patterns as $pattern) {
        $translation = array_shift($pattern);
        $patternLength = count($pattern);
        $match = true;
        for ($i = 0; $i < $patternLength; $i++) {
          if (!isset($pattern[$i]))
            continue;
          $arg = $args[$i];
          if (is_array($arg))
            $arg = count($arg);
          if (preg_match($pattern[$i], $arg) !== 1) {
            $match = false;
            break;
          }
        }
        if ($match)
          return $this->replacePlaceholders($translation, $args);
      }
    }
    return $this->replacePlaceholders($message, $args);
  }

  /**
   * @param string $message Message in english
   * @param string $singular Singular version of message in english
   * @param mixed ... Values
   */
  public function getN($message, $singular) {
    $args = array_slice(func_get_args(), 2);
    if (isset($this->messages[$message]))
      return call_user_func_array(array($this, 'get'), array_merge(array($message), $args));
    $num = $args[0];
    if (is_array($num))
      $num = count($num);
    if (abs($num) == 1)
      return $this->replacePlaceholders($singular, $args);
    return $this->replacePlaceholders($message, $args);
  }

  private $list = array();
  public function replaceList($matches) {
    $length = count($this->list);
    $list = '';
    for ($i = 0; $i < $length; $i++) {
      $list .= $this->list[$i];
      if ($i != ($length - 1)) {
        if ($i == ($length - 2))
          $list .= $matches[2];
        else
          $list .= $matches[1];
      }
    }
    return $list;
  }

  public function replacePlaceholders($message, $values = array()) {
    $length = count($values);
    $i = 1;
    foreach ($values as $value) {
      if (is_array($value)) {
        $this->list = $value;
        $message = preg_replace_callback(
          '/%' . $i . '\{(.*?)\}\{(.*?)\}/',
          array($this, 'replaceList'),
          $message
        );
      }
      else {
        $message = str_replace('%' . $i, $value, $message);
      }
      $i++;
    }
    return $message;
  }
}

$lang = new Localization();
$lang->set('Hello, World!', 'Hej, verden!');

//$lang->set('%1 pages', '%1 side', '/1/'); 
//$lang->set('%1 pages', '%1 sider'); 

// for russian
$lang->set('%1 pages', '%1 страниц', '/([05-9]|^1[0-9])$/');
$lang->set('%1 pages', '%1 страницы', '/[2-4]$/');
$lang->set('%1 pages', '%1 страница', '/1$/');

$lang->set('%1 comments', 'en kommentar', '/^1$/');
$lang->set('%1 comments', 'to kommentarer', '/^2$/');
$lang->set('%1 comments', '%1 kommentarer');

function s() {
  global $lang;
  return call_user_func_array(array($lang, 'get'), func_get_args());
}

function sn() {
  global $lang;
  return call_user_func_array(array($lang, 'getN'), func_get_args());
}

header('Content-Type: text/plain');

echo s('Hello, World!') . PHP_EOL;
for ($i = 0; $i < 30; $i++)
  echo s('%1 pages', $i) . PHP_EOL;

echo s('%1 comments', 2) . PHP_EOL;

echo sn('%1 categories', '%1 category', 0) . PHP_EOL;

$lang->set('Missing the "%1{", "}{" and "}" extensions', 'Mangler udvidelsen: "%1{}{}"', '/^1$/');
$lang->set('Missing the "%1{", "}{" and "}" extensions', 'Mangler udvideserne: "%1{", "}{" og "}"');

echo s('Missing the "%1{", "}{" and "}" extensions', array('mysql', 'pdo-mysql', 'sqlite')) . PHP_EOL;

echo sn('Missing the "%1{", "}{" and "}" extensions', 'Missing the "%1{", "}{" and "}" extension', array('sqlite')) . PHP_EOL;
