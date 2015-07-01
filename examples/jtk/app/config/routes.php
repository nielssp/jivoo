<?php
$this->root('action:App::index');
$this->error('action:App::notFound');

$this->match('**', 'action:Pages::view');
