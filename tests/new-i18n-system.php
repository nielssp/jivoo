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
    $args = func_get_args();
    array_shift($args);
    if (isset($this->messages[$message])) {
      $patterns = $this->messages[$message];
      foreach ($patterns as $pattern) {
        $translation = array_shift($pattern);
        $patternLength = count($pattern);
        $match = true;
        for ($i = 0; $i < $patternLength; $i++) {
          if (preg_match($pattern[$i], $args[$i]) !== 1) {
            $match = false;
            break;
          }
        }
        if ($match)
          return self::replacePlaceholders($translation, $args);
      }
    }
    return self::replacePlaceholders($message, $args);
  }

  public static function replacePlaceholders($message, $values = array()) {
    $length = count($values);
    for ($i = 0; $i < $length; $i++) {
      $message = str_replace('%' . ($i + 1), $values[$i], $message);
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

header('Content-Type: text/plain');

echo $lang->get('Hello, World!') . PHP_EOL;
for ($i = 0; $i < 30; $i++)
  echo $lang->get('%1 pages', $i) . PHP_EOL;

echo $lang->get('%1 comments', 2) . PHP_EOL;
