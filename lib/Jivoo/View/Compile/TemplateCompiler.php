<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

/**
 * Converts HTML templates to PHP templates.
 */
class TemplateCompiler {
  
  private $macros = array();
  
  public function __construct($defaultMacros = true) {
    if ($defaultMacros)
      $this->macros = DefaultMacros::getMacros();
  }
  
  public function addMacro($name, $function) {
    $this->macros[strtolower($name)] = $function;
  }
  
  public function compile($template) {
    $dom = new \simple_html_dom();
    $dom->load_file($template);
    
    $root = new InternalNode();
    
    $main = $dom->find('[j:main]', 0);
    if (isset($main)) {
      $root->append($this->convert($main));
    }
    else {
      foreach ($dom->find('*') as $html) {
        $root->append($this->convert($html));
      } 
    }
    
    $this->transform($root);
    
    return $root->__toString();
  }
  
  public function convert($node) {
    if ($node->tag === 'text' or $node->tag === 'unknown')
      return new TextNode($node->innertext . "\n");
    else if ($node->tag === 'comment') {
      if (preg_match('/^<!-- *\{(.*)\} *-->$/', $node->innertext, $matches) === 1) {
        return new PhpNode($matches[1], true);
      }
      return new TextNode('');
    }
    else {
      $output = new HtmlNode($node->tag);
      foreach ($node->attr as $name => $value) {
        if ($name[0] == 'j' and $name[1] == ':') {
          if ($value === true)
            $value = null;
          $output->addMacro(substr($name, 2), $value);
        }
        else {
          $output->setAttribute($name, new TextNode($value));
        }
      }
      foreach ($node->nodes as $child)
        $output->append($this->convert($child));
      return $output;
    }
  }
  
  public function transform(TemplateNode $node) {
    if ($node instanceof InternalNode) {
      foreach ($node->getChildren() as $child)
        $this->transform($child);
    }
    foreach ($node->macros as $macro => $value) {
      if (!isset($this->macros[$macro]))
        throw new \Exception(tr('Undefined macro: %1', $macro));
      $this->macros[$macro]($node, $value);
      if (!isset($node->parent))
        return;
    }
  }
}