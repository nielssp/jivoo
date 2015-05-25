<?php
include '../lib/Jivoo/Core/bootstrap.php';


include '../share/extensions/simplehtmldom/simple_html_dom.php';

$test = <<<'END'
<div>
  <!--{$posts = $Post->where('id > 5')}-->
  <div j:if="$true">
    <div class=test j:outertext=$post>test</div>
    <div j:tr j:if="$Auth->isLoggedIn()">
    Welcome back, <span j:outerText="$Auth->user->name">User<span>this is insignificant</span></span>!
    </div>
    <div j:tr>
    A more <span class="test" j:tr>advanced</span> <span j:outertext=$noun>example</span>
    </div>
  </div>
</div>
END;

class Node {
  public $type = 'html';
  public $text = '';
  public function __construct($type, $text) {
    $this->type = $type;
    $this->text = $text;
  }

  public static function html($html) {
    return new Node('html', $html);
  }
  
  public static function php($php) {
    return new Node('php', $php);
  }
  
  public static function phpE($php) {
    return new Node('phpe', $php);
  }
}

class Functions {
  static function _outertext($html, $content, $value) {
    return array('outer', array(Node::phpE($value)));
  }
  static function _innertext($html, $content, $value) {
    return array('inner', array(Node::phpE($value)));
  }
  static function _if($html, $content, $value) {
    $splits = explode("\0", $html->outertext);
    return array('outer', array_merge(
      array(Node::php('if (' . $value . '):')),
      array(Node::html($splits[0])),
      $content,
      array(Node::html($splits[1])),
      array(Node::php('endif'))
    ));
  }
  static function _tr($html, $content, $value) {
    $translate = '';
    $num = 1;
    $params = array();
    $before = array();
    foreach ($content as $node) {
      if ($node->type == 'html') {
        $translate .= $node->text;
      }
      else if ($node->type == 'phpe') {
        $translate .= '%' . $num;
        $params[] = $node->text;
        $num++;
      }
      else {
        $before[] = $node;
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    return array('inner', array_merge($before, array(Node::phpE('tr(' . var_export($translate, true) . $params . ')'))));
  }
}

function handleTag($html) {
  $content = convert($html);
  $html->innertext = "\0";
  foreach ($html->attr as $name => $value) {
    if ($name[0] == 'j' and $name[1] == ':') {
      $html->removeAttribute($name);
      $name = '_' . substr($name, 2);
      $output = Functions::$name($html, $content, $value);
      if ($output[0] == 'outer')
        return $output[1];
      else if ($output[0] == 'inner')
        $content = $output[1];
    }
  }
  $splits = explode("\0", $html->outertext);
  return array_merge(array(Node::html($splits[0])), $content, array(Node::html($splits[1])));
}

function convert($html) {
  $output = array();
  foreach ($html->nodes as $node) {
    if ($node->tag === 'text')
      $output[] = Node::html($node->innertext);
    else if ($node->tag === 'comment') {
      if (preg_match('/^<!-- *\{(.*)\} *-->$/', $node->innertext, $matches) === 1) {
        $output[] = Node::php($matches[1]); 
      }
    }
    else {
      $output = array_merge($output, handleTag($node));
    }
  }
  return $output;
}


$html = str_get_html($test);

$converted = convert($html->firstChild());

$phpTemplate = '';

foreach ($converted as $node) {
  if ($node->type == 'php') {
    $php = trim($node->text);
    $last = substr($php, -1);
    $semi = '';
    if ($last != ';' and $last != ':')
      $semi = ';';
    $phpTemplate .= '<?php ' . $node->text . $semi . ' ?>' . "\n";
  }
  else if ($node->type == 'phpe')
    $phpTemplate .= '<?php echo ' . $node->text . '; ?>' . "\n";
  else {
    $text = $node->text;
    if (substr($text, -1) == ' ')
      $text .= "\n";
    $phpTemplate .= $text;
  }
}

var_dump($phpTemplate);