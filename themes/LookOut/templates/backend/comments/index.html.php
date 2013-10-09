<?php
$this->extend('backend/layout.html');

$this->begin('records');
$this->first = true;
foreach ($comments as $comment) {
  $this->record = array(
    'title' => $comment->getPost()->title,
    'date' => $comment->date,
    'description' => $comment->content
  );
  $this->embed('backend/record.html');
  if ($this->first) {
    $this->first = false;
  }
}
$this->end();
?>
