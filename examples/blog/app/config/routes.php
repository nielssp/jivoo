<?php
$this->root('action:Posts::index');
$this->error('action:App::notFound');

$this->auto('action:App::login');
$this->auto('action:Posts::view');
