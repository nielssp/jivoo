<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\Selection\IBasicSelection;
use Jivoo\Helpers\Filtering\SelectionFilterVisitor;
use Jivoo\Helpers\Filtering\FilterParser;
use Jivoo\Helpers\Filtering\FilterScanner;
use Jivoo\Helpers\Filtering\RecordFilterVisitor;
use Jivoo\Models\IBasicModel;

// missing features:
//    "status=(draft|published)"  ... must be only one set of parentheses, and only OR's
//    "status!=published"
//    "title CONTAINS foo"
//    "created = 2014"    ... automatic interval from 2014-01-01 00:00:00 to 2014-12-31 23:59:59
//    "created > 2014-01-02 AND created BEFORE 2014-02-04"   ... aliases (date/created)?
//    "created IN july"
//    ON/IN/AT/!=/=/</>/<=/>=/BEFORE/AFTER
//    custom strtotime-function probably necessary.. should create intervals based on precission of input

class FilteringHelper extends Helper {

  private $scanner = null;
  private $parser = null;
  
  private $primary = array();
  
  public function __get($property) {
    switch ($property) {
      case 'query':
        return $this->request->query['filter'];
      case 'primary':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function __isset($property) {
    switch ($property) {
      case 'query':
        return isset($this->request->query['filter']);
      case 'primary':
        return isset($this->$property);
    }
    return parent::__isset($property);
  }
  
  public function addPrimary($column) {
    $this->primary[] = $column;
  }
  
  public function removePrimary($column) {
    $this->primary = array_diff($this->primary, array($column));
  }

  /**
   * 
   * @param IBasicSelection|IBasicRecord[] $selection
   * @param IBasicModel $model Model.
   * @return unknown|Condition
   */
  public function apply($selection, IBasicModel $model) {
    if (!isset($this->query) or empty($this->query))
      return $selection;
    if (!isset($this->scanner))
      $this->scanner = new FilterScanner();
    if (!isset($this->parser))
      $this->parser = new FilterParser();
    $tokens = $this->scanner->scan($this->query);
    if (count($tokens) == 0)
      return $selection;
    $root = $this->parser->parse($tokens);
    if ($selection instanceof IBasicSelection) {
      $visitor = new SelectionFilterVisitor($this, $model);
      $selection = $selection->where($visitor->visit($root));
      return $selection;
    }
    else {
      $result = array();
      $visitor = new RecordFilterVisitor($this);
      foreach ($selection as $record) {
        $visitor->setRecord($record);
        if ($visitor->visit($root))
          $result[] = $record;
      }
      return $result;
    }
  }
}