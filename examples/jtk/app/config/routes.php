<?php
$this->root('action:App::index');
$this->error('action:App::notFound');

$this->auto('action:App::colors');
$this->match('**', 'action:Pages::view', 4);
