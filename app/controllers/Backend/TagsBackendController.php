<?php
class TagsBackendController extends BackendController {
  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering',
    'Backend', 'Json', 'Bulk'
  );
  
  protected $modules = array('Editors');
  
  protected $models = array('Post', 'Comment', 'Tag');

  public function index() {
    $this->Backend->requireAuth('backend.tags.index');
    $this->title = tr('Tags');
    $this->tags = $this->Tag->all();
    $this->render();
  }
  
}