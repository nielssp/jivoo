<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Form;

use Jivoo\View\Compile\Macros;
use Jivoo\View\Compile\HtmlNode;
use Jivoo\View\Compile\TemplateNode;
use Jivoo\View\Compile\InternalNode;
use Jivoo\View\Compile\PhpNode;

/**
 * 
 */
class FormMacros extends Macros {
  /**
   * {@inheritdoc}
   */
  protected $namespace = 'f';
  
  public function _form(HtmlNode $node, TemplateNode $value = null) {
    $internal = new InternalNode();
    foreach ($node->getChildren() as $child)
      $internal->append($child->detach());
    $start = '$Form->form(';
    if ($node->hasAttribute('action')) {
      $start .= PhpNode::expr($node->getAttribute('action'))->code . ', ';
      $node->removeAttribute('action');
    }
    else {
      $start .= 'array(), ';
    }
    $start .= PhpNode::attributes($node)->code . ')';

    $internal->prepend(new PhpNode($start, true));
    $internal->append(new PhpNode('$Form->end()'));
    
    $node->replaceWith($internal);
  }
  
  public function _for(HtmlNode $node, TemplateNode $value) {
    $internal = new InternalNode();
    foreach ($node->getChildren() as $child)
      $internal->append($child->detach());
    $start = '$Form->formFor(' . PhpNode::expr($value)->code . ', ';
    if ($node->hasAttribute('action')) {
      $start .= PhpNode::expr($node->getAttribute('action'))->code . ', ';
      $node->removeAttribute('action');
    }
    else {
      $start .= 'array(), ';
    }
    $start .= PhpNode::attributes($node)->code . ')';
    
    $internal->prepend(new PhpNode($start, true));
    $internal->append(new PhpNode('$Form->end()'));
    
    $node->replaceWith($internal);
  }
  
  public function _field(HtmlNode $node, TemplateNode $value) {
    $internal = new InternalNode();
    foreach ($node->getChildren() as $child)
      $internal->append($child->detach());
    $start = '$field = $Form->field(' . PhpNode::expr($value)->code . ', ';
    $start .= PhpNode::attributes($node)->code . ')';
    
    $internal->prepend(new PhpNode($start, true));
    $internal->append(new PhpNode('$field->end()'));
    
    $node->replaceWith($internal);
  }
}