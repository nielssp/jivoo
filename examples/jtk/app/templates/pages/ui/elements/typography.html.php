<?php $this->view->data->title = tr('Typography'); ?>

<div class="grid-sm grid-1-1">
  <div class="cell">
<div class="block">
  <div class="block-header"><h3>Headings</h3></div>
  <div class="block-content">
<h1>Heading 1</h1>
<h2>Heading 2</h2>
<h3>Heading 3</h3>
<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
  </div>
</div>
  </div>
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Paragraphs</h3></div>
      <div class="block-content">

<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris. Donec sagittis mollis neque, et ornare turpis elementum at. Quisque metus diam, volutpat vel fermentum sed, ullamcorper eu dui. </p>

<p>
Vivamus cursus lorem sed vulputate tincidunt. Vivamus eget nibh iaculis, semper felis nec, aliquet orci. Morbi non sagittis est. Donec tincidunt ut metus sit amet fringilla. Suspendisse id est ac nulla luctus aliquam ullamcorper eget augue. Nam elementum pellentesque elit, ac dignissim est volutpat vel. </p>
  </div>
  </div>

  </div>
</div>

<div class="grid-md grid-1-1-1">
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Blockquotes</h3></div>
      <div class="block-content">
        <blockquote>
          Linux is awesome.
          <cite>Leonardo da Vinci</cite>
        </blockquote>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Preformatted</h3></div>
      <div class="block-content">
        <pre>#include &lt;stdio.h&gt;
int main() {
  printf("Hello, World!\n");
  return 0;
}</pre>
      </div>
    </div>
  </div>
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Links</h3></div>
      <div class="block-content">
        <p>
        <a href="#" title="Title">A normal link</a>
        <a href="#" title="Title" class="badge">A badge link</a>
        <?php echo $Icon->link('An icon link', 'url:#', 'cog', null, array('class' => 'badge badge-primary')); ?>
        </p>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Labels/badges</h3></div>
      <div class="block-content">
        <p>
          <span class="badge">Default</span>
          <span class="badge badge-primary">Primary</span>
          <span class="badge badge-info">Info</span>
          <span class="badge badge-success">Success</span>
          <span class="badge badge-warn">Warn</span>
          <span class="badge badge-error">Error</span>
        </p>
        <p>
          <?php echo $Icon->badge('Default', 'home'); ?>
          <?php echo $Icon->badge('Primary', 'flag', 'primary'); ?>
          <?php echo $Icon->badge('Info', 'info', 'info'); ?>
          <?php echo $Icon->badge('Success', 'checkmark', 'success'); ?>
          <?php echo $Icon->badge('Warn', 'warning', 'warn'); ?>
          <?php echo $Icon->badge('Error', 'close', 'error'); ?>
        </p>
      </div>
    </div>
  </div>
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Formatting</h3></div>
      <div class="block-content">
        <p>
          The following text <em>is emphasized</em>.
          The following text <strong>is strongly emphasized</strong>.
          The following text <mark>is highlighted</mark>.
          The following text <q>is an inline quotation</q>.
          This <del>text is deleted</del>, <ins>and this is inserted</ins>.
          This is a code fragment: <code>int main()</code>.
          This is a keyboard shortcut: <kbd>Ctrl</kbd>+<kbd>S</kbd>.
          <abbr title="Jivoo Toolkit">JTK</abbr> is an abbreviation.
        </p>
      </div>
    </div>
  </div>
</div>

<h2>Context highlighting</h2>

<div class="grid-sm grid-1-1-1">
<div class="cell">
<div class="block">
<div class="block-header">Colors</div>
<div class="block-content">
  <p class="success">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
  <p class="info">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
  <p class="warn">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
  <p class="error">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
</div>
</div>
</div>

<div class="cell">
<div class="block">
<div class="block-header">Backgrounds</div>
<div class="block-content">
  <p class="bg-success">Success</p>
  <p class="bg-info">Info</p>
  <p class="bg-warn">Warn</p>
  <p class="bg-error">Error</p>
</div>
</div>
</div>

<div class="cell">
<div class="block">
<div class="block-header">Info boxes</div>
<div class="block-content">

<div class="flash">
  <strong>Default</strong> Default
</div>
<div class="flash flash-success">
  <strong>Success</strong> Success
</div>

<div class="flash flash-error">
  <strong>Error</strong> Error
</div>

<div class="flash flash-warn">
  <strong>Warning</strong> Warning
</div>

<div class="flash flash-info">
  <strong>Information</strong> Information
</div>

<div class="flash flash-question">
  <strong>Question</strong> Question
</div>
</div>
</div>
</div>

</div>

<h2>Lists</h2>

<div class="grid-sm grid-1-1">
<div class="cell">
<div class="block">
<div class="block-header">Unordered lists</div>
<div class="block-content">
<ul>
  <li>Lorem</li>
  <li>Ipsum</li>
  <li>Dolor
    <ul>
      <li>Eget</li>
      <li>Neque
        <ul>
          <li>Erat</li>
        </ul>
      </li>
    </ul>
  </li>
  <li>Sit</li>
  <li>Amet</li>
</ul>
</div>
</div>
</div>
<div class="cell">
<div class="block">
  <div class="block-header"><h3>Ordered lists</h3></div>
<div class="block-content">
<ol>
  <li>Lorem</li>
  <li>Ipsum</li>
  <li>Dolor
    <ol>
      <li>Eget</li>
      <li>Neque
        <ol>
          <li>Erat</li>
        </ol>
      </li>
    </ol>
  </li>
  <li>Sit</li>
  <li>Amet</li>
</ol>
</div>
</div>
</div>
</div>


