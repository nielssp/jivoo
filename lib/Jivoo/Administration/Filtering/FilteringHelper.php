<?php
class FilteringHelper extends Helper {

  private $scanner = null;
  private $parser = null;
  
  public function __get($property) {
    switch ($property) {
      case 'query':
        return $this->request->query['filter'];
    }
    return parent::__get($property);
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'query':
        return isset($this->request->query['filter']);
    }
    return parent::__isset($property);
  }

  public function apply(IReadSelection $selection) {
    if (!isset($this->query) or empty($this->query))
      return $selection;
    if (!isset($this->scanner))
      $this->scanner = new FilterScanner();
    if (!isset($this->parser))
      $this->parser = new FilterParser();
    $tokens = $this->scanner->scan($this->query);
    $root = $this->parser->parse($tokens);
    $visitor = new SelectionVisitor('title');
    $selection = $selection->where($visitor->visit($root));
    return $selection;
  }
}