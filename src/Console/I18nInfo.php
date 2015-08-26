<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\SnippetBase;
use Jivoo\Core\Utilities;
use Jivoo\Core\I18n\Locale;
use Jivoo\Core\I18n\I18n;

/**
 * I18n language generator and editor.
 */
class I18nInfo extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form', 'Notify');
  
  /**
   * @var string
   */
  private $scope = 'app';
  
  /**
   * @var \Jivoo\Extensions\ExtensionInfo
   */
  private $extension = null;

  /**
   * @var \Jivoo\Themes\ThemeInfo
   */
  private $theme = null;

  /**
   * @var string
   */
  private $rootDir = null;
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->data->title = tr('I18n');

    if (isset($this->m->Extensions)) {
      $this->viewData['extensions'] = $this->m->Extensions->listAllExtensions();
    }
    else {
      $this->viewData['extensions'] = null;
    }
    if (isset($this->m->Themes)) {
      $this->viewData['themes'] = $this->m->Themes->listAllThemes();
    }
    else {
      $this->viewData['themes'] = null;
    }
    
    $this->viewData['dir'] = $this->p('app/languages');
    $this->viewData['project'] = $this->app->name . ' ' . $this->app->version;
    $this->rootDir = $this->p('app');
    if (isset($this->request->query['scope'])) {
      $scope = $this->request->query['scope'];
      if ($scope === 'lib') {
        $this->scope = 'lib';
        $this->viewData['dir'] = $this->p('Core/languages');
        $this->viewData['project'] = 'Jivoo ' . \Jivoo\VERSION;
        $this->rootDir = \Jivoo\PATH;
      }
      else if (strpos($scope, '-') !== false) {
        $scope = explode('-', $scope);
        if (isset($this->m->Extensions) and $scope[0] === 'extension') {
          if (isset($this->viewData['extensions'][$scope[1]])) {
            $this->scope = 'extension';
            $this->extension = $this->viewData['extensions'][$scope[1]];
            $this->viewData['dir'] = $this->extension->p($this->app, 'languages');
            $this->viewData['project'] = $this->extension->name . ' ' . $this->extension->version;
            $this->rootDir = $this->extension->p($this->app, '');
          }
        }
        else if (isset($this->m->Themes) and $scope[0] === 'theme') {
          if (isset($this->viewData['themes'][$scope[1]])) {
            $this->scope = 'theme';
            $this->theme = $this->viewData['themes'][$scope[1]];
            $this->viewData['dir'] = $this->theme->p($this->app, 'languages');
            $this->viewData['project'] = $this->theme->name . ' ' . $this->theme->version;
            $this->rootDir = $this->theme->p($this->app, '');
          }
        }
      }
    }
    $this->viewData['dirExists'] = Utilities::dirExists($this->viewData['dir']);
    if ($this->viewData['dirExists']) {
      $this->viewData['languages'] = $this->findLanguages($this->viewData['dir']);
    }
    return parent::before();
  }

  /**
   * {@inheritdoc}
   */
  public function post($data) {
    if (isset($data['generate']) and $this->viewData['dirExists']) {
      $gen = new LanguageGenerator();
      $gen->scanDir($this->rootDir);
      
      $fileName = $this->viewData['dir'] . '/en.pot';
      $file = fopen($fileName, 'w');
      if ($file) {
        fwrite($file, $gen->createPotFile($this->viewData['project']));
        fclose($file);
        $this->Notify->success = tr('Language created: %1', $fileName);
        return $this->refresh();
      }
      else {
        $this->Notify->error = tr('Unable to create file: %1', $fileName);
      }
    }
    else if (isset($data['new'])) {
      
    }
    return $this->get();
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this->render();
  }
  
  /**
   * Finds language files (*.lng.php) in a directory. 
   * @param string $dir Directory path.
   * @return \Jivoo\Core\I18n\Locale[] Array of localizations indexed by their
   * language code.
   */
  public function findLanguages($dir) {
    $files = scandir($dir);
    $languages = array();
    if ($files !== false) {
      foreach ($files as $file) {
        $ex = explode('.', $file);
        if (count($ex) == 2) {
          $tag = $ex[0];
          if ($ex[1] == 'po' or $ex[1] == 'pot') {
            $locale = Locale::readPo($dir . '/' . $file);
          }
          else if ($ex[1] == 'mo') {
            $locale = Locale::readMo($dir . '/' . $file);
          }
          else {
            continue;
          }
          if (!isset($languages[$tag])) {
            $languages[$tag] = array(
              'locale' => $locale,
              'po' => false,
              'mo' => false,
              'pot' => false
            );
          }
          $languages[$tag][$ex[1]] = true;
        }
      }
    }
    return $languages;
  }
}
