<?php
class ContentAdminHelper extends Helper {

  public function quickEdit($options) {
    $record = $options['record'];
    if ($record and $this->request->hasValidData()) {
      foreach ($options['edit'] as $field => $value) {
        $record->$field = $value;
      }
      if ($record->save()) {
        return $this->m->Routing->refresh();
      }
      else {
        $this->session->flash['error'][] = tr(
          'Unable to perform operation.'
        );
      }
    }
    return $this->view->fetch('admin/confirm.html', $options);
  }
  
  public function delete($options) {
    $record = $options['record'];
    if ($record and $this->request->hasValidData()) {
      $record->delete();
      return $this->m->Routing->refresh();
    }
    return $this->view->fetch('admin/confirm.html', $options);
  }
}