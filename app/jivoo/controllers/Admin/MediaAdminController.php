<?php
class MediaAdminController extends AdminController {
  
  public function before() {
    parent::before();
    $mediaDir = $this->p('media', '');
    if (!file_exists($mediaDir)) {
      mkdir($mediaDir);
    }
  }

  public function index() {
    $this->title = tr('Media');
    $subDir = '';
    $dir = scandir($this->p('media', $subDir));
    $files = array();
    if ($dir !== false) { 
      foreach ($dir as $file) {
        if ($file[0] != '.') {
          $files[] = array(
            'name' => $file,
            'relativePath' => $subDir . '/' . $file
          );
        }
      }
    }
    $this->files = $files;
    return $this->render();
  }
  
  public function add() {
    $this->title = tr('Add media');
    if ($this->request->hasValidData()) {
      if (isset($this->request->files['file'])) {
        $tmpName = $this->request->files['file']['tmp_name'];
        $name = basename($this->request->files['file']['name']);
        if (move_uploaded_file($tmpName, $this->p('media', $name))) {
          $this->session->flash->success = tr('File uploaded');
          $this->redirect('index');
        }
        else {
          $this->session->flash->error = tr('Upload failed %1', $this->request->files['file']['error']);
        }
      }
    }
    return $this->render();
  }
}
