<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Utilities;
use Jivoo\Core\Localization;

/**
 * I18n language generator and editor.
 */
class I18nInfo extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');
  
  private $scope = 'app';
  
  private $extension = null;
  
  private $theme = null;
  
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
    
    $this->viewData['dir'] = $this->p('app', 'languages');
    if (isset($this->request->query['scope'])) {
      $scope = $this->request->query['scope'];
      if ($scope === 'lib') {
        $this->scope = 'lib';
        $this->viewData['dir'] = $this->p('Core', 'languages');
      }
      else if (strpos($scope, '-') !== false) {
        $scope = explode('-', $scope);
        if (isset($this->m->Extensions) and $scope[0] === 'extension') {
          if (isset($this->viewData['extensions'][$scope[1]])) {
            $this->scope = 'extension';
            $this->extension = $this->viewData['extensions'][$scope[1]];
            $this->viewData['dir'] = $this->extension->p($this->app, 'languages');
          }
        }
        else if (isset($this->m->Themes) and $scope[0] === 'theme') {
          if (isset($this->viewData['themes'][$scope[1]])) {
            $this->scope = 'theme';
            $this->theme = $this->viewData['themes'][$scope[1]];
            $this->viewData['dir'] = $this->theme->p($this->app, 'languages');
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
      $gen->scanDir($this->viewData['dir']);
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
  
  public function findLanguages($dir) {
    $files = scandir($dir);
    $languages = array();
    if ($files !== false) {
      foreach ($files as $file) {
        $ex = explode('.', $file);
        if (count($ex) == 3 and $ex[2] == 'php' and $ex[1] == 'lng') {
          $tag = $ex[0];
          $localization = include $dir . '/' . $file;
          $languages[$tag] = $localization;
        }
      }
    }
    return $languages;
  }
}