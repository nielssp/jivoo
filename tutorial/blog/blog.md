# Creating a blog

## Prerequisites

- A web server
- PHP 5.2

## Creating an application

Create an empty directory called `app` (or anything else really). This is your
app-directory.
Create a new PHP-file in your app-dir called `app.php` (again, other names are
fine). This file will contain the distribution-configuration, i.e. the name of
the application, version, and which modules to load. A basic configuration looks
like this:

    <?php
    // app/app.php
    return array(
      'path' => str_replace('\\', '/', dirname(__FILE__)),
      'name' => 'My Web Application',
      'version' => '0.0.1',
      'modules' => array(
        'Core',
      ),
    );

The first option, `path`, is the absolute path to the app-directory of your
application. It is set automatically using the `__FILE__` constant, and this
should be fine for most applicaitons. `name` and `version` are the name and version
of your application. `modules` is an array
of modules that your application needs.

Next we need an entry-script to run our application. In an optimal setup this
file should be the only file accessible from the outside. We'll call it
`index.php` and put it next to our app-directory.

    <?php
    // index.php
    require_once 'lib/Core/bootstrap.php';

    Lib::import('Core');

    $app = new App(include 'app/app.php', basename(__FILE__));

    $environment = getenv('MYAPP_ENVIRONMENT');
    $environment || $environment = 'production';

    $app->run($environment);


## Skeleton

- index.php
- app/
  - app.php
  - assets/
  - config/
    - environments/
  - controllers/
  - helpers/
  - log/
  - models/
  - schemas/
  - templates/
- lib/
  - Core/

## Application controller

Creating the application controller:

    <?php
    // app/controllers/AppController.php
    class AppController extends Controller {
      public function index() {
        $this->render();
      }
    }
