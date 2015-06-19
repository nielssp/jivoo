<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

use Jivoo\Core\Logger;
/**
 * Converts HTML templates to PHP templates.
 */
class TemplateCompiler {
  /**
   * @var callable[] Map of macro functions.
   */
  private $macros = array();
  
  /**
   * Construct compiler.
   * @param bool $defaultMacros Whether to add default macros provided by
   * {@see DefaultMacros}.
   */
  public function __construct($defaultMacros = true) {
    if ($defaultMacros)
      $this->macros = DefaultMacros::getMacros();
  }
  
  /**
   * Add a macro.
   * @param string $name Lowercase macro name.
   * @param callable $function A function accepting two parameters: the target
   * {@see HtmlNode}, and the value of the macro attribute (string or null).
   */
  public function addMacro($name, $function) {
    $this->macros[strtolower($name)] = $function;
  }
  
  /**
   * Compile a template file by reading it, converting the DOM using
   * {@see convert()}, then applying macros using {@see transform()}.
   * @param string $template Template file path.
   * @return string PHP template content. 
   */
  public function compile($template) {
    Logger::debug('Compiling template: ' . $template);
    $dom = new \simple_html_dom();
    $file = file_get_contents($template);
    if ($file === false)
      throw new \Exception(tr('Could not read template: %1', $template));
    if (!$dom->loadFile($file))
      throw new \Exception(tr('Could not parse template: %1', $template));
    
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
  
  /**
   * Convert HTML DOM to a template node.
   * @param \simple_html_dom_node $node DOM node.
   * @return TemplateNode Template node.
   */
  public function convert(\simple_html_dom_node $node) {
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
  
  /**
   * Apply macros to a template node.
   * @param TemplateNode $node Node.
   * @throws \Exception If unknown macro.
   */
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