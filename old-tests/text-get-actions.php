<?php

include '../../LAB/LabTest.php';

class Controller {
  private function somethingPrivate() {
    
  }
  
  protected function pro() {
    
  }
  
  public function preRender() {
    
  }
}


class BackendController extends Controller {
  private function derp() {
    
  }
  
  public function preRender() {
    
  }
  
  public function index() {
    
  }
  
  public function action2() {
    
  }
}


class PostsBackendController extends BackendController {
  public function index() {
    
  }
  
  public function action3() {
    
  }
}


function get_actions_1($class) {
  $classMethods = get_class_methods($class);
  $parentMethods = get_class_methods(get_parent_class($class));
  return array_diff($classMethods, $parentMethods);
}

function get_actions_2($class) {
  $reflection = new ReflectionClass($class);
  $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
  $actions = array();
  foreach ($methods as $method) {
    if ($method->class == $class) {
      $actions[] = $method->name;
    }
  }
  return $actions;
}

$test = new LabTest();

$rounds = 1000;

$class = 'PostsBackendController';

$test->testFunction($rounds, 'get_actions_1', $class);
$test->dumpResult();

$test->testFunction($rounds, 'get_actions_2', $class);
$test->dumpResult();

$test->report();





