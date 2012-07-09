<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  public function index() {
    $select = SelectQuery::create()
      ->orderByDescending('date');
    $this->Pagination->setCount(Post::count());

    $this->Pagination->paginate($select);

    $this->posts = Post::all($select);

    if ($this->request->isAjax()) {
      $jsonPosts = array();
      foreach ($this->posts as $post) {
        $jsonPosts[] = $post->json();
      }
      echo '[' . implode(',', $jsonPosts) . ']';
    }
    else {
      $this->render();
    }
  }

  public function view($post) {
    $this->reroute();

    $this->post = Post::find($post);

    if (!$this->post) {
      $this->render('404.html');
      return;
    }
    $this->title = $this->post->title;
    if ($this->request->isAjax()) {
      if ($this->request->isPost()) {
        echo json_encode($this->request->data);
      }
      else {
        echo json_encode($this->request->query);
      }
//      echo $this->post->json();
    }
    else {
      $this->render();
    }
  }
  
  public function manage() {
    $this->render('not-implemented.html');
  }

  public function add() {
    $examplePost = Post::create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%', $this->m->Routes->getLink($examplePost));
    $examplePost = NULL;
    $this->nameInPermalink = count($exampleLink) >= 2;
    $this->beforePermalink = $exampleLink[0];
    $this->afterPermalink = $exampleLink[1];
    if ($this->request->isPost()) {
      $this->post = Post::create($this->request->data['post']);
      if (isset($this->request->data['publish'])) {
        $this->post->state = 'published';
      }
      else {
        $this->post->state = 'draft';
      }
      if ($this->post->isValid()) {
        $this->post->save();
        $this->post->createAndAddTags($this->post->tags);
        if ($this->post->state == 'published') {
          $this->redirect($this->post);
        }
        else {
          new LocalNotice(tr('Post successfully created'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->post->getErrors() as $field => $error) {
          new LocalWarning($field . ': ' . $error);
        }
      }
    }
    else {
      $this->post = Post::create();
    }
    $this->title = tr('New post');
    $this->render('backend/edit-post.html');
  }

  public function edit($post) {
    $this->render('not-implemented.html');
  }

  public function delete($post) {
    $this->render('not-implemented.html');
  }

  public function tagIndex() {
    $this->render('not-implemented.html');
  }
  
  public function viewTag($tag) {
    $this->tag = Tag::first(SelectQuery::create()
      ->where('name = ?', $tag)
    );
    $count = $this->tag->getPosts(SelectQuery::create()->count());

    $this->Pagination->setCount($count);

    $select = SelectQuery::create()
      ->orderByDescending('date');

    $this->Pagination->paginate($select);

    $this->posts = $this->tag->getPosts($select);

    if ($this->request->isAjax()) {
      $jsonPosts = array();
      foreach ($this->posts as $post) {
        $jsonPosts[] = $post->json();
      }
      echo '[' . implode(',', $jsonPosts) . ']';
    }
    else {
      $this->render('posts/index.html');
    }
  }
  
  public function commentIndex($post) {
    $this->render('not-implemented.html');
  }
  
  public function viewComment($post, $comment) {
    $this->render('not-implemented.html');
  }
  
}
