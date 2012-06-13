<?php

class Event {
  private $functions = array();
  public function attach($function) {
    $this->functions[] = $function;
  }

  public function detach($function) {
    $key = array_search($function, $this->functions);
    if ($key !== false) {
      unset($this->functions[$key]);
    }
  }

  public function update($object, EventArgs $args) {
    foreach ($this->functions as $function) {
      call_user_func($function, $object, $args);
    }
  }
}

class EventArgs {
  private $value;

  public function __construct($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

}

class Publisher {
  public $temperatureUpdateEvent;

  private $temperature;

  public function __construct() {
    $this->temperatureUpdateEvent = new Event();
  }

  public function setTemperature($new) {
    $this->temperature = $new;
    $this->temperatureUpdateEvent->update($this, new EventArgs($new));
  }

}

function updated($object, EventArgs $args) {
  echo "New temp: " . $args->getValue();
}


$publisher = new Publisher();

$publisher->temperatureUpdateEvent->attach('updated');

$publisher->setTemperature(32);