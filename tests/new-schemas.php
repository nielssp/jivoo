<?php

// example schema file:

return array(
  Field::integer('id', Field::UNSIGNED | Field::AUTO_INCREMENT | Field::NOT_NULL),
  Field::string('username', 255, Field::NOT_NULL),
  Field::string('password', 255, Field::NOT_NULL),
  Field::string('session', 255, Field::NOT_NULL),
  Field::integer('hue', Field::UNSIGNED | Field::NOT_NULL),
  Field::datetime('created_at'),
  Field::datetime('updated_at'),
  Index::primary('id'),
  Index::unqiue('username', 'username').
  Index::index('session', 'session')
);