<?php
if (!class_exists('EventArgs'))
  return;
class ConcreteEventArgs extends EventArgs {
  protected $foo = 23;
  protected $bar = 42;
  protected $baz = 'Hello, World';
}
