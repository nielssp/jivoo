<?php 
echo $Skin->import('jivoo/skin.css');

// Dropdown

$Css->addMixin('dropdown', function($Css) use ($Skin) {
  $Css('li ul')->css(array(
    'background-color' => $Skin->subMenuBg,
    'box-shadow' => '0px 2px 4px ' . $Skin->subMenuShadow
  ));
  $Css('li ul li')->color = $Skin->subMenuFg;
  $Css('li ul li:hover')->css(array(
    'background-color' => $Skin->navBg,
    'color' => $Skin->navHlFg
  ));
});

$Css('.dropdown ul')->apply('dropdown')->css(array(
  'background-color' => $Skin->subMenuBg,
  'box-shadow' => '0px 2px 4px ' . $Skin->subMenuShadow
))->find('& > li')->css('color', $Skin->subMenuFg)->find('&:hover')->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->navHlFg
));

$Css('.dropdown:hover > a')->css(array(
  'background-color' => $Skin->navHlBg,
  'color' => $Skin->navHlFg,
  'border-color' => $Skin->navHlFg
));

// Topnav
$Css('header > ul')->apply('dropdown');
$Css('header > ul > li')->color = $Skin->headerFg;
$Css('header > ul > li > a')->find('&, &:link, &:active, &:visited, &:hover')->css(array(
  'color' => 'inherit',
  'background' => 'inherit'
));
$Css('header > ul > li:hover')->css(array(
  'color' => $Skin->headerHlFg,
  'background-color' => $Skin->headerHlBg
));

// Sidenav

$Css('body.menu-open header .toggle-menu')->backgroundColor = $Skin->headerHlBg;

$Css('body.menu-open nav')->backgroundColor = $Skin->navBg;

$toggle = $Css('header .toggle-menu');
$toggle->find('&:link, &:active, &:visited')->color = $Skin->headerFg;
$toggle->find('&:hover')->color = $Skin->headerHlFg;

$Css('nav')->color = $Skin->navFg;
$Css('nav h1')->color = $Skin->navFg;
$Css('nav > ul > li > ul')->apply('dropdown');
$li = $Css('nav > ul > li > ul > li');
$li->color = $Skin->navFg;
$li->find('&:hover')->css(array(
  'background-color' => $Skin->navHlBg,
  'color' => $Skin->navHlFg,
));
$li->find('& > a.current')->css(array(
  'background-color' => $Skin->navCuBg,
  'color' => $Skin->navCuFg,
));

// Widgets

$Css('.table-selection')->css(array(
  'background-color' => '#ffe',
  'border-top-color' => $Skin->inputBorder
));

$Css('.table-settings-box')->css(array(
  'color' => $Skin->subMenuFg,
  'background-color' => $Skin->subMenuBg,
  'box-shadow' => '0px 2px 4px ' . $Skin->subMenuShadow,
));

$Css('.skin-list > .skin:hover')->backgroundColor = $Skin->tableHlBg;

// Theme

$tag = $Css('.publish .tags .tag');
$tag->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->navFg
));
$tag->find('&:hover')->backgroundColor = $Skin->navHlBg;
$tag->find('a, a:link, a:visited')->color = $Skin->navFg;
$tag->find('a:hover')->color = $Skin->navHlFg;

echo $Css;
