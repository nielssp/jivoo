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
  
  private $transformations = array();
  
  public function __construct($defaultTransformations = true) {
    if ($defaultTransformations)
      $this->transformations = DefaultTransformations::getTransformations();
  }
  
  public function addTransformation($name, $transformer) {
    $this->transformations[strtolower($name)] = $transformer;
  }
  
  public function compile($template) {
    $dom = file_get_html($template);
    
    $root = new InternalNode();
    
    foreach ($dom->find('*') as $html) {
      $root->append($this->convert($html));
    } 
    
    $this->transform($root);
    
    return $root->__toString();
  }
  
  public function convert($node) {
    if ($node->tag === 'text' or $node->tag === 'unknown')
      return new TextNode($node->innertext);
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
          $output->addTransformation(substr($name, 2), $value);
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
    foreach ($node->transformations as $transformation => $value) {
      if (!isset($this->transformations[$transformation]))
        throw new \Exception(tr('Undefined transformation: %1', $transformation));
      $this->transformations[$transformation]($node, $value);
      if (!isset($node->parent))
        return;
    }
  }
}