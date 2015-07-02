<?php
use Jivoo\Extensions\ExtensionModule;
use Jivoo\Routing\RenderEvent;
use Jivoo\Core\Logger;

class SassUpdater extends ExtensionModule {
  
  protected $modules = array('Assets', 'Routing');
  
  protected function init() {
    $this->m->Routing->attachEventHandler('beforeRender', array($this, 'runSass'));
  }
  
  public function runSass(RenderEvent $event) {
    if ($event->response->type == 'text/html') {
      $cwd = getcwd();
      foreach ($this->m->Assets->getAssetDirs() as $assetDir) {
        $dir = $this->p($assetDir['key'], $assetDir['path']);
        if (is_dir($dir)) {
          Logger::log('sass: ' . realpath($dir));
          chdir(realpath($dir));
          $r = exec('sass --update sass:css');
          if (strpos($r, 'error') !== false)
            Logger::warning($r);
          chdir($cwd);
        }
      }
    }
  }
}
