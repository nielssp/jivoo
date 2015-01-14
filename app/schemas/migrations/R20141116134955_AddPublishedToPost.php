<?php
class R20141116134955_AddPublishedToPost extends Migration {

  public function up() {
    $this->addColumn('Post', 'published', DataType::dateTime(true));
    $this->Post->where('status = %PostStatus', 'published')
      ->set('published = created')
      ->update();
  }

  public function down() {
    $this->deleteColumn('Post', 'published');
  }
}
