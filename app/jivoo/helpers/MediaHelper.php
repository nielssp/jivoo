<?php
class MediaHelper extends Helper {
  protected $modules = array('Content', 'Assets');
  
  protected function init() {
    $this->m->Content->extensions->add(
      'media',
      array('file' => null, 'alt' => null),
      array($this, 'mediaFunction')
    );
    $this->m->Content->extensions->add(
      'figure',
      array('file' => null, 'alt' => null, 'caption' => ''),
      array($this, 'figureFunction')
    );
  }

  public function mediaFunction($params) {
    $file = $this->m->Assets->getAsset('media', $params['file']);
    if (!isset($file))
      return 'invalid file';
    if (!isset($params['alt']))
      $params['alt'] = $params['file'];
    return '<img src="' . $file .'" alt="' . h($params['alt']) . '" />';
  }
  
  public function figureFunction($params) {
    return '<figure>' . $this->mediaFunction($params)
      . '<figcaption>' . $params['caption'] . '</figcaption></figure>';
  }
}