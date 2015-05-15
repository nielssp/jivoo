<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Table;

use Jivoo\Jtk\JtkSnippet;
use Jivoo\Models\IBasicModel;
use Jivoo\Models\Selection\IBasicSelection;
use Jivoo\Models\IModel;
use Jivoo\Models\IBasicRecord;

/**
 * A data table snippet.
 */
class DataTableSnippet extends JtkSnippet {
  protected $helpers = array('Icon', 'Filtering', 'Pagination');
  
  protected $objectType = 'Jivoo\Jtk\Table\DataTable';
  
  public function compareRecords(IBasicRecord $a, IBasicRecord $b) {
    $field = $this->object->sortBy->field;
    if ($a->$field == $b->$field)
      return 0;
    if ($this->object->sortBy->descending) {
      if (is_numeric($a->$field))
        return $b->$field - $a->$field;
      return strcmp($b->$field, $a->$field);
    }
    else {
      if (is_numeric($a->$field))
        return $a->$field - $b->$field;
      return strcmp($a->$field, $b->$field);
    }
  }
  
  public function get() {
    $o = $this->object;
    assume($o->model instanceof IBasicModel);
    if ($o->model instanceof IModel) {
      if (!($o->selection instanceof IBasicSelection))
        $o->selection = $o->model;
      if (!isset($o->primaryKey))
        $o->primaryKey = $o->model->getAiPrimaryKey(); // TODO: more general?
    }
    if (count($o->columns) == 0) {
      foreach ($o->model->getFields() as $field)
        $o->columns->appendNew($o->model->getLabel($field), $field);
      $o->columns->objectAt(0)->primary = true;
    }
    
    $o->selection = $this->Filtering->apply($o->selection);
    
    if (count($o->sortOptions) == 0)
      $o->sortOptions = $o->columns;
    
    $sortBy = $o->sortOptions->find(function($column) {
      return $column->default;
    });
    if (!isset($sortBy))
      $sortBy = $o->sortOptions->objectAt(0);

    if (isset($this->request->query['sortBy'])) {
      $field = $this->request->query['sortBy'];
      $sortBy2 = $o->sortOptions->find(function($column) use ($field) {
        return $column->field === $field;
      });
      if (isset($sortBy2))
        $sortBy = $sortBy2;
    }
    $sortBy->selected = true;
    if (isset($this->request->query['order']))
      $sortBy->descending = ($this->request->query['order'] == 'desc');
    $o->sortBy = $sortBy;
    
    if ($o->selection instanceof IBasicSelection) {
      if ($sortBy->descending)
        $o->selection = $o->selection->orderByDescending($sortBy->field);
      else
        $o->selection = $o->selection->orderBy($sortBy->field);
    }
    else {
      assume(is_array($o->selection));
      $records = $o->selection;
      usort($records, array($this, 'compareRecords'));
      $o->selection = $records;
    }
    
    $o->selection = $this->Pagination->paginate($o->selection, $o->rowsPerPage);
    return $this->render();
  }
}

