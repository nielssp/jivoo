<?php
use Jivoo\View\Compile\TemplateCompiler;
use Jivoo\View\Compile\InternalNode;
include '../lib/Jivoo/Core/bootstrap.php';


include '../share/extensions/simplehtmldom/simple_html_dom.php';

$test = <<<'END'
<div class="template">
<div class="pagination" j:if="!$Pagination->isFirst()">
  <a j:if="!$Pagination->isLast()" href="#" j:href="$Paginmation->nextLink()" j:tr>
    &#8592; Older posts
  </a>
  <div class="right">
    <a href="#" j:href="$Paginmation->prevLink()" j:tr>
      Newer posts &#8594;
    </a>
  </div>
</div>
<div class="post" j:foreach="$posts as $post">
  <h1>
    <a href="#" j:href="$post" j:text="$post->title">
      Title goes here
    </a>
  </h1>
  <div j:outertext="$Format->html($post, 'content')">
    Content goes here
  </div>
  <div class="byline">
    <span j:tr>Posted on <span j:outerText="fdate($post->created)">date</span></span>
    |
    <!-- {$comments = $post->comments->where('status = %CommentStatus', 'approved')->count()} -->
    <a href="#" j:if="$comments == 0" j:href="$this->mergeRoutes($post, array('fragment' => 'comment'))" j:tr>
      Leave a comment
    </a>
    <a href="#" j:else j:href="$this->mergeRoutes($post, array('fragment' => 'comments'))" j:tn="%1 comment">
      <span j:outerText="$comments">0</span> comments
    </a>
  </div>
</div>
<div class="pagination">
  <a href="#" j:if="!$Pagination->isLast()" j:href="$Pagination->nextLink()" j:tr>&#8592; Older posts</a>
  <div href="#" j:if="!$Pagination->isFirst()" class="right">
    <a j:href="$Paginmation->prevLink()" j:tr>Newer posts &#8594;</a>
  </div>
</div>
  
</div>
END;



$compiler = new TemplateCompiler();

$html = file_get_html('../../jivoocms/user/themes/awesome-alien/templates/posts/index.html');

foreach ($html->find('*') as $h) {
  var_dump($h->tag);
}

$converted = $compiler->convert($html);

$root = new InternalNode();
$root->append($converted);

$compiler->transform($root);

echo '<pre>';
echo h($root->__toString());
echo '</pre>';