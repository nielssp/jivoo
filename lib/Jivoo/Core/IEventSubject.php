<?php
interface IEventSubject {
  public function attachEventHandler($name, $callback);
  public function attachEventListener(IEventListener $listener);
  public function detachEventHandler($name, $callback);
  public function detachEventListener(IEventListener $listener);
  public function hasEvent($name);
  public function getEvents();
}