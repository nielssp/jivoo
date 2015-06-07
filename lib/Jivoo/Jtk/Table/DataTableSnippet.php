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
use Jivoo\Models\Form;
use Jivoo\Models\DataType;

/**
 * A data table snippet.
 */
class DataTableSnippet extends JtkSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Icon', 'Filtering', 'Pagination', 'Form');

  /**
   * {@inheritdoc}
   */
  protected $objectType = 'Jivoo\Jtk\Table\DataTable';
  
  /**
   * Compare two records based on the chosen sorting.
   * @param IBasicRecord $a First record.
   * @param IBasicRecord $b Second record.
   * @return int The order of the two rows. I.e. 0 if they are equal, a
   * positive integer if the first record should come after the second record,
   * or a negative integer otherwise.
   */
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

  /**
   * {@inheritdoc}
   */
  public function before() {
    parent::before();
    if (isset($this->request->cookies['data-table-per-page']))
      $this->object->rowsPerPage = intval($this->request->cookies['data-table-per-page']);
    if (isset($this->request->cookies['data-table-density']))
      $this->object->density = $this->request->cookies['data-table-density'];
    else
      $this->object->density = 'medium';

    $tableSettings = new Form('tableSettings');
    $tableSettings->addField('perPage', DataType::integer(DataType::UNSIGNED));
    $tableSettings->__set('perPage', $this->object->rowsPerPage);
    $tableSettings->addField('density', DataType::enum(array('low', 'medium', 'high')));
    $tableSettings->__set('density', $this->object->density);
    $this->viewData['tableSettings'] = $tableSettings;
  }

  /**
   * {@inheritdoc}
   */
  public function post($data) {
    if (isset($data['tableSettings'])) {
      $this->viewData['tableSettings']->addData($data['tableSettings']);
      if ($this->viewData['tableSettings']->isValid()) {
        $this->request->cookies['data-table-per-page'] = $this->viewData['tableSettings']->perPage;
        $this->request->cookies['data-table-density'] = $this->viewData['tableSettings']->density;
        $this->refresh();
      }
    }
    return $this->get();
  }

  /**
   * {@inheritdoc}
   */
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
    
    $o->selection = $this->Filtering->apply($o->selection, $o->model);
    
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

