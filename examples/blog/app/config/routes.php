<?php
$this->root('action:Posts::index');
$this->error('action:App::notFound');

$this->auto('action:App::login');
$this->auto('action:App::logout');

$this->resource('action:Posts');
