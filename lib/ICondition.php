<?php

interface ICondition {
  /* and() and or() */
  public function __call($method, $args);

  public function hasClauses();

  public function where($clause);

  public function andWhere($clause);

  public function orWhere($clause);

  public function addVar($var);
}
