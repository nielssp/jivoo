<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\SnippetBase;
use Jivoo\Models\Form;
use Jivoo\Models\DataType;
use Jivoo\Core\Json;
use Jivoo\Setup\InstallerSnippet;

/**
 * Configure application.
 */
class ManifestInstaller extends InstallerSnippet {
  /**
   * @var Form Configuration form.
   */
  private $configForm;

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form', 'Jtk');

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('welcome', true);
    $this->appendStep('configure');
  }  
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->configForm = new Form('Configure');
    $this->configForm->addString('name', tr('Application name'));
    $this->configForm->addString('version', tr('Version'));
    $this->configForm->addField('modules', DataType::object(), tr('Modules'));

    $this->view->data->availableModules = $this->getModules();
    $this->view->data->configForm = $this->configForm;
    $this->view->data->title = tr('Configure application');
    return null;
  }
  
  /**
   * Installer step: Welcome to the Jivoo framework.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function welcome($data = null) {
    if (isset($data))
      return $this->next();
    $this->viewData['appDir'] = $this->p('app', '');
    return $this->render();
  }

  /**
   * Installer step: Configure application manifest, create directories, and
   * copy files.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function configure($data = null) {
    if (isset($data)) {
      $this->configForm->addData($data['Configure']);
      $manifest = array(
        'name' => $this->configForm->name,
        'version' => $this->configForm->version,
        'modules' => array_merge(
          array('Assets', 'Helpers', 'Models', 'Routing', 'View'),
          array_values($this->configForm->modules)
        ),
        'install' => 'Jivoo\Setup\DefaultInstaller',
        'update' => 'Jivoo\Setup\DefaultUpdater'
      );
      mkdir($this->p('app', ''));
      mkdir($this->p('app', 'config'));
      mkdir($this->p('app', 'config/environments'));
      $this->installFile('Core', 'config/environments/development.php');
      $this->installFile('Core', 'config/environments/production.php');
      mkdir($this->p('user', ''));
      mkdir($this->p('log', ''));
      mkdir($this->p('state', ''));
      $file = fopen($this->p('app', 'app.json'), 'w');
      if ($file) {
        fwrite($file, Json::prettyPrint($manifest));
        fclose($file);
        return $this->next();
      }
    }
    else {
      $this->configForm->name = $this->app->name;
      $this->configForm->version = $this->app->version;
      $this->configForm->modules = array(
        'Controllers', 'Snippets', 'Databases', 'Migrations', 'ActiveModels',
        'Extensions', 'Themes',
        'AccessControl', 'Setup', 'Jtk', 'Console', 'Generators', 'Content'
      );
    }
    return $this->render();
  }
  
  /**
   * Install a file from a modules default-directory into the app-directory. 
   * @param string $module Module name.
   * @param string $file Relative file path.
   */
  private function installFile($module, $file) {
    copy(
      \Jivoo\PATH . '/' . $module . '/default/' . $file,
      $this->p('app', $file)
    );
  }

  /**
   * Get list of Jivoo modules.
   * @return string Module names.
   */
  private function getModules() {
    $files = scandir(\Jivoo\PATH);
    $modules = array();
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.')
          continue;
        if (file_exists(\Jivoo\PATH . '/' . $file . '/' . $file . '.php')) {
          $modules[] = $file;
        }
      }
    }
    return $modules;
  }
}