<?php
// app/schemas/postsSchema.php
class postsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::AUTO_INCREMENT | Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addString('title', 255, Schema::NOT_NULL);
    $this->addText('content', Schema::NOT_NULL);
    $this->addInteger('created_at', Schema::NOT_NULL | Schema::UNSIGNED);

    $this->setPrimaryKey('id');
    $this->addIndex('created_at', 'created_at');
  }
}
