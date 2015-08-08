<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View\Compile;

use Jivoo\View\InvalidMacroException;
use Jivoo\View\InvalidTemplateException;

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
      $this->addMacros(new DefaultMacros());
  }
  
  /**
   * Add a macro.
   * @param string $name Lowercase macro name.
   * @param callable $function A function accepting two parameters: the target
   * {@see HtmlNode}, and the value of the macro attribute (string or null).
   * @param string $namepsace Macro namespace, default is 'j'.
   */
  public function addMacro($name, $function, $namespace = 'j') {
    $this->macros[$namespace . ':' . strtolower($name)] = $function;
  }
  
  /**
   * Add multiple macros.
   * @param callable[]|Macros $macros Mapping of macro names to functions.
   * @param string $namepsace Macro namespace, default is 'j'.
   */
  public function addMacros($macros, $namespace = 'j') {
    if ($macros instanceof Macros) {
      $this->addMacros($macros->getMacros(), $macros->getNamespace());
      return;
    }
    foreach ($macros as $name => $function)
      $this->addMacro($name, $function, $namespace);
  }
  
  /**
   * Compile a template file by reading it, converting the DOM using
   * {@see convert()}, then applying macros using {@see transform()}.
   * @param string $template Template file path.
   * @return string PHP template content. 
   * @throws InvalidTemplateException If template is inaccessible or invalid.
   */
  public function compile($template) {
    $dom = new \simple_html_dom();
    $file = file_get_contents($template);
    if ($file === false)
      throw new InvalidTemplateException(tr('Could not read template: %1', $template));
    if (!$dom->load($file, true, false))
      throw new InvalidTemplateException(tr('Could not parse template: %1', $template));
    
    $root = new InternalNode();
    
    $main = $dom->find('[j:main]', 0);
    if (isset($main)) {
      $root->append($this->convert($main));
    }
    else {
      foreach ($dom->find('*, text') as $html) {
        if ($html->parent->tag != 'root')
          continue;
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
      return new TextNode($node->innertext);
    else if ($node->tag === 'comment') {
      if (preg_match('/^<!-- *\{(.*)\} *-->$/ms', $node->innertext, $matches) === 1) {
        return new PhpNode($matches[1], true);
      }
      return new TextNode('');
    }
    else {
      $output = new HtmlNode($node->tag);
      foreach ($node->attr as $name => $value) {
        if (strpos($name, ':') === false) {
          $output->setAttribute($name, new TextNode($value));
        }
        else {
//           list($ns, $name) = explode(':', $name, 2);
          if ($value === true)
            $value = null;
          $output->addMacro($name, $value);
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
   * @throws InvalidMacroException If macro is unknown.
   */
  public function transform(TemplateNode $node) {
    if ($node instanceof InternalNode) {
      foreach ($node->getChildren() as $child)
        $this->transform($child);
    }
    foreach ($node->macros as $macro => $value) {
      if (!$node->hasMacro($macro))
        continue;
      if (!isset($this->macros[$macro]))
        throw new InvalidMacroException(tr('Undefined macro: %1', $macro));
      call_user_func($this->macros[$macro], $node, $value);
      if (!isset($node->parent))
        return;
    }
  }
}
