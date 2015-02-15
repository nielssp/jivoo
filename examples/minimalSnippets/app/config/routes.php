<?php
$this->root('snippet:FrontPage');
$this->error('snippet:NotFound');

$this->match('**', 'snippet:Page', 4);
