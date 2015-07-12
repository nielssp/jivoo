<?php

$Skin->setDefault(array(
  'primaryBg' => '#2272cc',
  'primaryHlBg' => '#6da6e7',
  'primaryFg' => '#fff'
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
  'subMenuFg' => '#333',
  'subMenuShadow' => '#ababab',
  'inputBorder' => '#d9d9d9',
  'inputShadow' => '#e6e6e6',
  'inputHlBorder' => $Skin->primaryHlBg,
  'inputErrorBg' => '#fee',
  'inputErrorBorder' => '#f55',
  'tableHlBg' => '#f7f7f7'
));

$Css->addMixin('button', function($Css) use ($Skin) {
  $Css('&, &:link, &:visited')->css(array(
    'background-color' => $Skin->navBg,
    'border-color' => $Skin->navFg,
    'color' => $Skin->navFg
  ));
  $Css('&:hover, &:active')->css(array(
    'background-color' => $Skin->navHlBg,
    'border-color' => $Skin->navHlFg,
    'color' => $Skin->navHlFg
  ));
  $Css('&[disabled]')->css(array(
    'background-color' => $Skin->navDisBg,
    'border-color' => $Skin->navDisFg,
    'color' => $Skin->navDisFg
  ));  
});

// Base

$Css('body')->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->mainFg
));

$Css('a:link, a:active, a:visited')->color = $Skin->linkFg;
$Css('a:hover')->color = $Skin->linkHlFg;

// Layout

$Css('header')->css(array(
  'background-color' => $Skin->headerBg,
  'color' => $Skin->headerFg
));
$Css('#main')->backgroundColor = $Skin->mainBg;
$Css('footer')->borderTopColor = $Skin->navBg;

// Form

$Css('.button, button, input[type=button], input[type=reset], input[type=submit]')->apply('button');

$input = $Css('input[type=text], input[type=email], input[type=password], input[type=date],
input[type=time], input[type=datetime], textarea, select');
$input->css(array(
  'border-color' => $Skin->inputBorder,
  'box-shadow' => 'inset 0 1px 2px ' . $Skin->inputShadow
));
$input('&:focus')->css(array(
  'border-color' => $Skin->inputHlBorder,
  'box-shadow' => '0 0 1px ' . $Skin->inputHlBorder
));
$input('&[data-error], &.error')->css(array(
  'background-color' => $Skin->inputErrorBg,
  'border-color' => $Skin->inputErrorBorder
));

$input = $Css('input[type=checkbox], input[type=radio]');
$input->css(array(
  'border-color' => $Skin->inputBorder,
  'box-shadow' => 'inset 0 1px 2px ' . $Skin->inputShadow
));
$input('&:checked:before')->color = $Skin->primaryBg;
$input('&:focus')->css(array(
  'border-color' => $Skin->inputHlBorder,
  'box-shadow' => '0 0 1px ' . $Skin->inputHlBorder
));
$Css('input[type=radio]:checked:before')->backgroundColor = $Skin->primaryBg; 

// Table
$th = $Css('table thead th, table tfoot th');
$th->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->navDisFg
));
$th->find('a, label')->find('&, &:link, &:visited')->color = $Skin->navFg;
$th->find('a, label')->find('&:hover, &:active')->css(array(
  'background-color' => $Skin->navHlBg,
  'color' => $Skin->navHlFg,
  'border-color' => $Skin->navHlFg
));
$Css('table tbody tr td')->borderTopColor = $Skin->inputShadow;
$Css('table tbody tr td:hover')->backgroundColor = $Skin->tableHlBg;

echo $Css;