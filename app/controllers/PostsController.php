<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  public function index() {
    $select = SelectQuery::create()
      ->orderByDescending('date');
    $this->Pagination->setCount(Post::count());

    $this->Pagination->paginate($select);

    $this->posts = Post::all($select);

    $this->render();
  }

  public function view($post) {
    $this->reroute();

    $this->post = Post::find($post);
    
    $select = SelectQuery::create()->orderBy('date');

    $this->Pagination->setLimit(10);
    
    $this->Pagination->setCount($this->post->comments);
    $this->Pagination->paginate($select);

    if (!$this->post) {
      $this->render('404.html');
      return;
    }
    if ($this->request->isPost()) {
      $this->newComment = Comment::create($this->request->data['comment']);
      $this->newComment->setPost($this->post);
      $this->newComment->ip = $this->request->ip;
      if ($this->newComment->isValid()) {
        $this->newComment->save();
        $this->post->comments += 1;
        $this->post->save();
        $this->Pagination->setCount($this->post->comments);
        $this->refresh(
          array('page' => $this->Pagination->getPages()),
          'comment' . $this->newComment->id
        );
      }
    }
    else {
      $this->newComment = Comment::create();
    }
    
    $this->comments = $this->post->getComments($select);
    
    $this->title = $this->post->title;
    $this->render();
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
          new LocalWarning($this->post->getFieldLabel($field) . ': ' . $error);
        }
      }
    }
    else {
      $this->post = Post::create();
    }
    $this->title = tr('New post');
    $this->render('posts/edit.html');
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

    $this->Pagination->setCount($this->tag->countPosts());

    $select = SelectQuery::create()
      ->orderByDescending('date');

    $this->Pagination->paginate($select);

    $this->posts = $this->tag->getPosts($select);

    $this->title = $this->tag->tag;

    $this->render('posts/index.html');
  }
  
  public function commentIndex($post) {
    $this->render('not-implemented.html');
  }
  
  public function viewComment($post, $comment) {
    $this->render('not-implemented.html');
  }
  
}
