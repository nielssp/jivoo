<?php
include '../lib/Jivoo/Core/bootstrap.php';


include '../share/extensions/simplehtmldom/simple_html_dom.php';

$test = <<<'END'
<div>
<div class="pagination" j:if="!$Pagination->isFirst()">
  <a j:if="!$Pagination->isLast()" href="#" j:href="$Paginmation->nextLink()" j:tr>
    &#8592; Older posts
  </a>
  <div class="right">
    <a href="#" j:href="$Paginmation->prevLink()" j:tr>
      Newer posts &#8594;
    </a>
  </div>
</div>
<div class="post" j:foreach="$posts as $post">
  <h1>
    <a href="#" j:href="$post" j:text="$post->title">
      Title goes here
    </a>
  </h1>
  <div j:outertext="$Format->html($post, 'content')">
    Content goes here
  </div>
  <div class="byline">
    <span j:tr>Posted on <span j:outerText="fdate($post->created)">date</span></span>
    |
    <!-- {$comments = $post->comments->where('status = %CommentStatus', 'approved')->count()} -->
    <a href="#" j:if="$comments == 0" j:href="$this->mergeRoutes($post, array('fragment' => 'comment'))" j:tr>
      Leave a comment
    </a>
    <a href="#" j:else j:href="$this->mergeRoutes($post, array('fragment' => 'comments'))" j:tn="%1 comment">
      <span j:outerText="$comments">0</span> comments
    </a>
  </div>
</div>
<div class="pagination">
  <a href="#" j:if="!$Pagination->isLast()" j:href="$Pagination->nextLink()" j:tr>&#8592; Older posts</a>
  <div href="#" j:if="!$Pagination->isFirst()" class="right">
    <a j:href="$Paginmation->prevLink()" j:tr>Newer posts &#8594;</a>
  </div>
</div>
  
</div>
END;

abstract class TemplateNode {
  private $transformations = array();
  
  /**
   * @var InternalNode
   */
  protected $parent = null;
  
  protected $next = null;
  
  protected $prev = null;
  
  public function __construct() { }
  
  public function __get($property) {
    switch ($property) {
      case 'transformations':
      case 'parent':
      case 'next':
      case 'prev':
        return $this->$property;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  public function __isset($property) {
    return $this->__get($property) !== null;
  }
  
  public function detach() {
    assume(isset($this->parent));
    $this->parent->remove($this);
  }
  
  public function replaceWith(TemplateNode $node) {
    assume(isset($this->parent));
    $this->parent->replace($this, $node);
  }
  
  public function addTransformation($transformation, $value = null) {
    $this->transformations[$transformation] = $value;
  }
}

class PhpNode extends TemplateNode {
  private $code = '';
  private $statement = false;
  
  public function __construct($code, $statement = false) {
    parent::__construct();
    $this->code = $code;
    $this->statement = $statement;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'code':
      case 'statement':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function __toString() {
    if ($this->statement) {
      $code = trim($this->code);
      $last = substr($code, -1);
      $semi = '';
      if ($last != ';' and $last != ':')
        $semi = ';';
      return '<?php ' . $this->code . $semi . ' ?>' . "\n";
    }
    else {
      return '<?php echo ' . $this->code . '; ?>' . "\n";
    }
  }
}

class IfNode extends TemplateNode {
  private $condition = '';
  private $then;
  private $else;
  
  public function __construct($condition, TemplateNode $then = null) {
    parent::__construct();
    $this->condition = $condition;
    $this->then = new InternalNode();
    $this->else = new InternalNode();
    if (isset($then))
      $this->then->append($then);
  }
  
  public function __get($property) {
    switch ($property) {
      case 'then':
      case 'else':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function __toString() {
    $code = '<?php if (' . $this->condition . '): ?>' . "\n";
    $code .= $this->then->__toString();
    if (count($this->else) > 0) {
      $code .= '<?php else: ?>' . "\n";
      $code .= $this->else->__toString();
    }
    $code .= '<?php endif; ?>' . "\n";
    return $code;
  }
}

class InternalNode extends TemplateNode implements Countable {
  protected $content = array();
  
  public function count() {
    return count($this->content);
  }
  
  public function append(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    if ($this->content !== array()) {
      $node->prev = array_slice($this->content, -1)[0];
      $node->prev->next = $node;
    }
    $this->content[] = $node;
    return $this;
  }
  
  public function prepend(TemplateNode $node) {
    assume(!isset($node->parent));
    $node->parent = $this;
    if ($this->content !== array()) {
      $node->next = $this->content[0];
      $node->next->prev = $node;
    }
    $this->content = array_merge(array($node), $this->content);
    return $this;
  }
  
  public function remove(TemplateNode $node) {
    assume($node->parent === $this);
    $this->content = array_diff($this->content, array($node));
    $node->parent = null;
    if (isset($node->next))
      $node->next->prev = $node->prev;
    if (isset($node->prev))
      $node->prev->next = $node->next;
    $node->next = null;
    $node->prev = null;
    return $this;
  }

  public function replace(TemplateNode $node, TemplateNode $replacement) {
    assume($node->parent === $this);
    assume(!isset($replacement->parent));
    $offset = array_search($node, $this->content, true);
    $this->content[$offset] = $replacement;
    $node->parent = null;
    if (isset($node->next)) {
      $node->next->prev = $replacement;
      $replacement->next = $node->next;
    }
    if (isset($node->prev)) {
      $node->prev->next = $replacement;
      $replacement->prev = $node->prev;
    }
    $node->next = null;
    $node->prev = null;
    return $this;
  }
  
  public function clear() {
    foreach ($this->content as $node) {
      $node->parent = null;
      $node->next = null;
      $node->prev = null;
    }
    $this->content = array();
    return $this;
  }
  
  public function getChildren() {
    return $this->content;
  }
  
  public function __toString() {
    $output = '';
    foreach ($this->content as $node)
      $output .= $node->__toString();
    return $output;
  }
}

class ForeachNode extends InternalNode {
  private $foreach;

  public function __construct($foreach) {
    parent::__construct();
    $this->foreach = $foreach;
  }

  public function __toString() {
    $code = '<?php foreach (' . $this->foreach . '): ?>' . "\n";
    $code .= parent::__toString();
    $code .= '<?php endforeach; ?>' . "\n";
    return $code;
  }
}

class TextNode extends TemplateNode {
  private $text;

  public function __construct($text) {
    parent::__construct();
    $this->text = $text;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'text':
        return $this->$property;
    }
    return parent::__get($property);
  }

  public function __toString() {
    $text = $this->text;
    if ($text[0] == ' ')
      $text = "\n" . ltrim($text);
    if (substr($text, -1) == ' ')
      $text = rtrim($text) . "\n"; 
    return $text;
  }
}

class HtmlNode extends InternalNode {
  private $tag = '';
  private $attributes = array();
  private $selfClosing = false;
  
  public function __construct($tag) {
    parent::__construct();
    $this->tag = $tag;
  }
  
  public function setAttribute($attribute, TemplateNode $value = null) {
    $this->attributes[$attribute] = $value;
  }
  
  public function hasAttribute($attribute) {
    return array_key_exists($attribute, $this->attributes);
  }
  
  public function getAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      return $this->attributes[$attribute];
    return null;
  }
  
  public function removeAttribute($attribute) {
    if (isset($this->attributes[$attribute]))
      unset($htis->attributes[$attribute]);
  }
  
  public function __toString() {
    $output = '<' . $this->tag;
    foreach ($this->attributes as $name => $value) {
      $output .= ' ' . $name;
      if (isset($value))
        $output .= '="' . $value . '"';
    }
    if (count($this->content) == 0 and $this->selfClosing)
      return $output . ' />';
    $output .= '>';
    $output .= parent::__toString();
    $output .= '</' . $this->tag . '>';
    return $output;
  }
}

class transformers {
  static function _outertext(HtmlNode $node, $value) {
    $node->replaceWith(new PhpNode($value));
  }
  static function _innertext(HtmlNode $node, $value) {
    $node->clear()->append(new PhpNode($value));
  }
  static function _if(HtmlNode $node, $value) {
    if (!isset($value)) {
      $prev = $node->prev;
      $between = array();
      do {
        if ($prev instanceof IfNode) {
          assume(count($prev->else) == 0);
          $between = array_reverse($between);
          foreach ($between as $betweenNode) {
            $betweenNode->detach();
            $prev->then->append($betweenNode);
          }
          $node->detach();
          $prev->then->append($node);
          return;
        }
        $between[] = $prev;
        $prev = $prev->prev;
      } while (isset($prev));
      throw new Exception('todo');
    }
    $ifNode = new IfNode($value);
    $node->replaceWith($ifNode);
    $ifNode->then->append($node);
  }
  static function _else(HtmlNode $node, $value) {
    $prev = $node->prev;
    $between = array();
    do {
      if ($prev instanceof IfNode) {
        $between = array_reverse($between);
        foreach ($between as $betweenNode) {
          $betweenNode->detach();
          $prev->then->append($betweenNode);
        }
        $node->detach();
        $prev->else->append($node);
        return;
      }
      $between[] = $prev;
      $prev = $prev->prev;
    } while (isset($prev));
    throw new Exception('todo');
  }
  static function _foreach(HtmlNode $node, $value) {
    if (!isset($value)) {
      if ($node->prev instanceof ForeachNode) {
        $foreachNode = $node->prev;
        $node->detach();
        $foreachNode->append($node);
        return;
      }
      throw new Exception('todo');
    }
    $foreachNode = new ForeachNode($value);
    $node->replaceWith($foreachNode);
    $foreachNode->append($node);
  }
  static function _href(HtmlNode $node, $value) {
    $node->setAttribute('href', new PhpNode('$this->link(' . $value . ')'));
  }
  static function _text(HtmlNode $node, $value) {
    self::_innertext($node, $value);
  }
  static function _tr(HtmlNode $node, $value) {
    $translate = '';
    $num = 1;
    $params = array();
    $before = array();
    foreach ($node->getChildren() as $child) {
      if ($child instanceof TextNode) {
        $translate .= $child ->text;
      }
      else if ($child instanceof PhpNode and !$child->statement) {
        $translate .= '%' . $num;
        $params[] = $child->code;
        $num++;
      }
      else {
        throw new Exception('not implemented');
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    $node->clear();
    $phpNode = new PhpNode('tr(' . var_export($translate, true) . $params . ')');
    $node->append($phpNode);
  }
  static function _tn(HtmlNode $node, $value) {
    $translate = '';
    $num = 1;
    $params = array();
    $before = array();
    foreach ($node->getChildren() as $child) {
      if ($child instanceof TextNode) {
        $translate .= $child ->text;
      }
      else if ($child instanceof PhpNode and !$child->statement) {
        $translate .= '%' . $num;
        $params[] = $child->code;
        $num++;
      }
      else {
        throw new Exception('not implemented');
      }
    }
    if (count($params) == 0)
      $params = '';
    else
      $params = ', ' . implode(', ', $params);
    $translate = trim($translate);
    $node->clear();
    $phpNode = new PhpNode('tn(' . var_export($translate, true) . ', ' . var_export($value, true) . $params . ')');
    $node->append($phpNode);
  }
}


function convert($html) {
  $output = new HtmlNode($html->tag);
  foreach ($html->attr as $name => $value) {
    if ($name[0] == 'j' and $name[1] == ':') {
      if ($value === true)
        $value = null;
      $output->addTransformation(substr($name, 2), $value);
    }
    else {
//       if (preg_match('/^ *\{(.*)\} *$/', $value, $matches) === 1)
//         $output->setAttribute($name, new PhpNode($value));
//       else
        $output->setAttribute($name, new TextNode($value));
    }
  }
  foreach ($html->nodes as $node) {
    if ($node->tag === 'text')
      $output->append(new TextNode($node->innertext));
    else if ($node->tag === 'comment') {
      if (preg_match('/^<!-- *\{(.*)\} *-->$/', $node->innertext, $matches) === 1) {
        $output->append(new PhpNode($matches[1], true));
      }
    }
    else {
      $output->append(convert($node));
    }
  }
  return $output;
}

function transform(TemplateNode $node) {
  if ($node instanceof InternalNode) {
    foreach ($node->getChildren() as $child)
      transform($child);
  }
  foreach ($node->transformations as $transformation => $value) {
    $transformation = '_' . $transformation;
    transformers::$transformation($node, $value);
    if (!isset($node->parent))
      return;
  }
}


$html = str_get_html($test);

$converted = convert($html->firstChild());

$root = new InternalNode();
$root->append($converted);

transform($root);

echo '<pre>';
echo h($root->__toString());
echo '</pre>';