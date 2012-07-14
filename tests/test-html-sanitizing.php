<?php

include('../app/essentials.php');

function output($text) {
  echo '<div style="font-size:10px;margin:5px;width:500px;padding:4px;border:4px dashed #ccc;">' . PHP_EOL . PHP_EOL;
  echo $text . PHP_EOL . PHP_EOL;
  echo '</div>' . PHP_EOL . PHP_EOL;
}

$comment = <<<END
<a href="javascript:alert('xss');" onmouseover="alert('xss');">Naked ladies</a><br/>
  <br>
  <P>Hello, World</p>
  </div><div>
  <div style=color:blue>this is green</div>
  <meta name="derp" value="lort"/>
  <a hReF="http://apakoh.dk" style="color:brown;">Hello</a>
END;

$comment2 = <<<END
<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>
<IMG """><SCRIPT>alert("XSS")</SCRIPT>">
<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>
<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>
<IMG SRC="jav ascript:alert('XSS');">
<IMG
SRC
=
"
j
a
v
a
s
c
r
i
p
t
:
a
l
e
r
t
(
  '
  X
  S
  S
  '
)
"
>
<img title="displays >" src="big.gif">

<IMG SRC=" &#14;  javascript:alert('XSS');">
<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>
<BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>
<<SCRIPT>script src="http://ha.ckers.org/xss.js">
</<script>scriipt>
<IMG SRC="javascript:alert('XSS')"
  <BR SIZE="&{alert('XSS')}">
  ¼script¾alert(¢XSS¢)¼/script¾
  <DIV STYLE="background-image: url(javascript:alert('XSS'))">
  <DIV STYLE="width: expression(alert('XSS'));">
END;

output($comment);

output(nl2br(strip_tags($comment)));

$allow = array(
  'div' => array(),
  'br' => array(),
  'a' => array(
    'href' => array('url' => TRUE)
  ),
  'p' => array(),
  'img' => array(
    'src' => array('url' => TRUE)
  ),
);

$format = new Encoder();

$format->allowTag('div');
$format->allowTag('br');
$format->allowTag('a');
$format->allowAttribute('a', 'href');
$format->validateAttribute('a', 'href', 'url', TRUE);
$format->allowTag('p');
$format->allowTag('img');
$format->allowAttribute('img', 'src');
$format->validateAttribute('img', 'src', 'url', TRUE);
$format->appendAttributes('a', 'rel="nofollow"');

output($format->encode($comment, $allow));

output($format->encode($comment2, $allow));

