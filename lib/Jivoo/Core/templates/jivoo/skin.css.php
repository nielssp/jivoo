<?php

$Skin->setDefault(array(
  'primaryBg' => '#2272cc',
  'primaryHlBg' => '#99c1ee',
  'primaryFg' => '#fff',
));

$Skin->setDefault(array(
  'headerBg' => $Skin->primaryBg,
  'headerFg' => $Skin->primaryFg,
));

$Skin->setDefault(array(
  'headerHlBg' => 'rgba(0, 0, 0, 0.15)',
  'headerHlFg' => $Skin->headerFg,
));

$Skin->setDefault(array(
  'linkFg' => $Skin->primaryBg,
  'linkHlFg' => $Skin->primaryHlBg,
  'navBg' => '#eee',
  'navFg' => '#444',
  'navHlBg' => '#ddd',
  'navHlFg' => $Skin->primaryBg,
  'navCuBg' => $Skin->primaryBg,
  'navCuFg' => $Skin->primaryFg,
  'navDisBg' => '#f1f1f1',
  'navDisFg' => '#999',
  'mainBg' => '#fff',
  'mainFg' => '#333',
  'subMenuBg' => '#f7f7f7',
  'subMenuFg' => '#333'
));


$Css('body')->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->mainFg
));

$Css('a:link, a:active, a:visited')->color = $Skin->linkFg;
$Css('a:hover')->color = $Skin->linkHlFg;

$Css('header')->css(array(
  'background-color' => $Skin->headerBg,
  'color' => $Skin->headerFg
));
$Css('#main')->backgroundColor = $Skin->mainBg;
$Css('footer')->borderTopColor = $Skin->navBg;


echo $Css;