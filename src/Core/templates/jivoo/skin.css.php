<?php

$Skin->setDefault(array(
  'primary' => '#2272cc',
  'grey' => '#727272',
  'info' => '#22aacc',
  'success' => '#22cc22',
  'warning' => '#cc7222',
  'error' => '#cc2222'
));
$Skin->setDefault(array(
  'dark' => $Css->shade($Skin->primary, 40),
  'light' => $Css->tint($Skin->primary, 40)
));

$Skin->setDefault(array(
  'primaryBg' => $Skin->primary
));

$Skin->setDefault(array(
  'primaryFg' => $Css->contrasted($Skin->primaryBg, '#222', '#fff'),
  'primaryHlBg' => $Css->lighten($Skin->primaryBg, 25)
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
  'linkFg' => $Skin->primary,
  'linkHlFg' => $Skin->light,
  'navBg' => $Css->setLightness($Css->setSaturation($Skin->primary, 11), 85),
  'navFg' => '#444',
  'navHlBg' => $Css->setLightness($Css->setSaturation($Skin->primary, 11), 90),
  'navHlFg' => $Skin->primaryBg,
  'navCuBg' => $Skin->primaryBg,
  'navCuFg' => $Skin->primaryFg,
  'navDisBg' => '#f1f1f1',
  'navDisFg' => '#999',
  'mainBg' => '#f1f1f1',
  'mainFg' => '#333',
  'subMenuBg' => $Css->setLightness($Css->setSaturation($Skin->primary, 11), 76),
  'subMenuFg' => '#333',
  'subMenuShadow' => '#ababab',
  'inputBorder' => '#d9d9d9',
  'inputShadow' => '#e6e6e6',
  'inputHlBorder' => $Skin->primaryHlBg,
  'inputErrorBg' => '#fee',
  'inputErrorBorder' => '#f55',
  'codeBg' => $Skin->navBg,
  'codeFg' => $Skin->warning,
  'blockBg' => '#f7f7f7',
));

$Skin->setDefault(array(
  'mainHeaderBg' => $Skin->blockBg,
  'mainHeaderFg' => '#666',
  'mainHeaderShadow' => '#d8d8d8',
  'tableHlBg' => $Css->darken($Skin->mainBg, 3),
));

$Css->addMixin('flashColor', function($flash, $color) use($Css) {
  $flash->css(array(
    'border-color' => $color
  ));
  $flash('&:before, h1, h2, h2, h3, h4, h5, h6, strong')->css(array(
    'color' => $color
  ));
});

$Css->addMixin('buttonColor', function($button, $color) use($Css) {
  $button->css(array(
    'background-color' => $Css->desaturate($Css->lighten($color, 10), 20),
    'border-color' => $Css->darken($color, 30),
    'color' => '#fff'
  ));
  $button('&:hover, &.active')->css(array(
    'background-color' => $color,
    'border-color' => $Css->darken($color, 20),
    'color' => '#fff'
  ));
  $button('&[disabled]')->find('&, &:hover, &:active')->css(array(
    'background-color' => $Css->desaturate($Css->lighten($color, 20), 50),
    'border-color' => $Css->desaturate($color, 50),
    'color' => '#eee'
  ));
});

$Css->addMixin('button', function($button) use($Skin, $Css) {
  $button('&, &:link, &:visited')->css(array(
    'background-color' => $Skin->navBg,
    'border-color' => $Skin->navFg,
    'color' => $Skin->navFg
  ));
  $button('&:hover, &.active')->css(array(
    'background-color' => $Skin->navHlBg
  ));
  $button('&:focus')->css(array(
    'box-shadow' => '0 0 2px 1px ' . $Css->toString($Css->lighten($Skin->primary, 10))
  ));
  $button('&:hover, &:active')->css(array(
    'border-color' => $Skin->navHlFg,
    'color' => $Skin->navHlFg
  ));
  $button('.active, &:active')->boxShadow = 'inset 0 4px 5px 0 rgba(0, 0, 0, 0.15)';
  $button('&[disabled]')->css(array(
    'background-color' => $Skin->navDisBg,
    'border-color' => $Skin->navDisFg,
    'color' => $Skin->navDisFg
  ));
  $button('&.button-primary')->apply('buttonColor', $Skin->primary);
  $button('&.button-light')->apply('buttonColor', $Skin->light);
  $button('&.button-dark')->apply('buttonColor', $Skin->dark);
  $button('&.button-info')->apply('buttonColor', $Skin->info);
  $button('&.button-success')->apply('buttonColor', $Skin->success);
  $button('&.button-warning')->apply('buttonColor', $Skin->warning);
  $button('&.button-error')->apply('buttonColor', $Skin->error);
});

// Base

$Css('body')->css(array(
  'background-color' => $Skin->navBg,
  'color' => $Skin->mainFg
));

// Typography

$Css('a, a:link, a:active, a:visited')->color = $Skin->linkFg;
$Css('a:hover')->color = $Skin->linkHlFg;

$Css('h1, h2, h3, h4, h5, h6')->find('& > small')->color = $Css->lighten($Skin->mainFg, 40);

$Css('mark')->backgroundColor = '#ffffcc';
$Css('del')->color = '#722222';
$Css('ins')->color = '#227222';

$Css('pre, code')->backgroundColor = $Skin->codeBg;
$Css('pre, code')->color = $Skin->codeFg;

$Css('.muted')->color = $Skin->grey;
$Css('.primary')->color = $Skin->primary;
$Css('.light')->color = $Skin->light;
$Css('.dark')->color = $Skin->dark;
$Css('.info')->color = $Skin->info;
$Css('.success')->color = $Skin->success;
$Css('.warning')->color = $Skin->warning;
$Css('.error')->color = $Skin->error;

$Css('.badge, .bg, .bg-muted, .bg-primary, .bg-light, .bg-dark, .bg-info, .bg-success, .bg-warning, .bg-error')->css(array(
  'background-color' => $Skin->grey,
  'color' => '#fff'
));

$Css('.bg-primary, .badge-primary')->backgroundColor = $Skin->primary;
$Css('.bg-light, .badge-light')->backgroundColor = $Skin->light;
$Css('.bg-dark, .badge-dark')->backgroundColor = $Skin->dark;
$Css('.bg-info, .badge-info')->backgroundColor = $Skin->info;
$Css('.bg-success, .badge-success')->backgroundColor = $Skin->success;
$Css('.bg-warning, .badge-warning')->backgroundColor = $Skin->warning;
$Css('.bg-error, .badge-error')->backgroundColor = $Skin->error;

// Blocks

$block = $Css('.block');
$block->backgroundColor = $Skin->blockBg;
$block('&&-default > &-header, &&-muted > &-header')->backgroundColor = $Skin->grey;
$block('&&-primary > &-header')->backgroundColor = $Skin->primary;
$block('&&-light > &-header')->backgroundColor = $Skin->light;
$block('&&-dark > &-header')->backgroundColor = $Skin->dark;
$block('&&-info > &-header')->backgroundColor = $Skin->info;
$block('&&-success > &-header')->backgroundColor = $Skin->success;
$block('&&-warning > &-header')->backgroundColor = $Skin->warning;
$block('&&-error > &-header')->backgroundColor = $Skin->error;

// Buttons

$buttons = '.button, button, input[type=button], input[type=reset], input[type=submit]'; 

$Css($buttons)->apply('button');

// Flash

$flash = $Css('.flash');
$flash('&')->apply('flashColor', $Skin->grey);
$flash('&&-primary')->apply('flashColor', $Skin->primary);
$flash('&&-light')->apply('flashColor', $Skin->light);
$flash('&&-dark')->apply('flashColor', $Skin->dark);
$flash('&&-info')->apply('flashColor', $Skin->info);
$flash('&&-success')->apply('flashColor', $Skin->success);
$flash('&&-warning')->apply('flashColor', $Skin->warning);
$flash('&&-error')->apply('flashColor', $Skin->error);

// Progress

$Css('.progress .progress-bar')->backgroundColor = $Skin->grey;
$progress = $Css('.progress');
$progress('&.primary .progress-bar')->backgroundColor = $Skin->primary;
$progress('&.light .progress-bar')->backgroundColor = $Skin->light;
$progress('&.dark .progress-bar')->backgroundColor = $Skin->dark;
$progress('&.info .progress-bar')->backgroundColor = $Skin->info;
$progress('&.success .progress-bar')->backgroundColor = $Skin->success;
$progress('&.warning .progress-bar')->backgroundColor = $Skin->warning;
$progress('&.error .progress-bar')->backgroundColor = $Skin->error;

// Layout

$Css('header')->css(array(
  'background-color' => $Skin->headerBg,
  'color' => $Skin->headerFg
));
$Css('#main')->backgroundColor = $Skin->mainBg;
$Css('#main #main-container > h1:first-child')->css(array(
  'background-color' => $Skin->mainHeaderBg,
  'color' => $Skin->mainHeaderFg,
  'box-shadow' => '1px 1px 2px ' . $Css->toString($Skin->mainHeaderShadow)
));
$Css('footer')->borderTopColor = $Skin->navBg;

// Form

$inputs = 'input[type=text], input[type=email], input[type=password], input[type=date],
input[type=time], input[type=datetime], textarea, select';

$Css->addMixin('fieldColor', function($field, $color) use($Css, $inputs, $buttons) {
  $field('& > label:first-child, .help')->color = $color;
  $input = $field($inputs);
  $input->borderColor = $color;
  $input('&:focus')->borderColor = $Css->lighten($color, 20);
  $field('.input-group-text')->css(array(
    'border-color' => $color,
    'background-color' => $Css->lighten($color, 40)
  ));
  $field('.input-group-button')->find($buttons)->apply('buttonColor', $color);
});

$input = $Css($inputs);
$input->css(array(
  'border-color' => $Skin->inputBorder,
  'box-shadow' => 'inset 0 1px 2px ' . $Css->toString($Skin->inputShadow)
));
$input('&:focus')->css(array(
  'border-color' => $Skin->inputHlBorder,
  'box-shadow' => '0 0 1px ' . $Css->toString($Skin->inputHlBorder)
));
$input('&[data-error], &.error')->css(array(
  'border-color' => $Skin->error
))->find('&:focus')->borderColor = $Css->lighten($Skin->error, 20);
$input('&:disabled')->backgroundColor = $Css->darken($Skin->mainBg, 3);


$input = $Css('input[type=checkbox], input[type=radio]');
$input->css(array(
  'border-color' => $Skin->inputBorder,
  'box-shadow' => 'inset 0 1px 2px ' . $Css->toString($Skin->inputShadow)
));
$input('&:checked:before')->color = $Skin->primaryBg;
$input('&:focus')->css(array(
  'border-color' => $Skin->inputHlBorder,
  'box-shadow' => '0 0 1px ' . $Css->toString($Skin->inputHlBorder)
));
$Css('input[type=radio]:checked:before')->backgroundColor = $Skin->primaryBg; 
$input('&:disabled')->backgroundColor = $Css->darken($Skin->mainBg, 3);

$field = $Css('.field');
$field('& .help')->color = $Skin->grey;
$field('&&-primary')->apply('fieldColor', $Skin->grey);
$field('&&-primary')->apply('fieldColor', $Skin->primary);
$field('&&-light')->apply('fieldColor', $Skin->light);
$field('&&-dark')->apply('fieldColor', $Skin->dark);
$field('&&-info')->apply('fieldColor', $Skin->info);
$field('&&-success')->apply('fieldColor', $Skin->success);
$field('&&-warning')->apply('fieldColor', $Skin->warning);
$field('&&-error')->apply('fieldColor', $Skin->error);

// Table
$th = $Css('table thead th, table tfoot th');
$th->css(array(
  'color' => $Skin->navFg,
  'border-color' => $Skin->navDisFg
));
$th->find('a, label')->find('&, &:link, &:visited')->color = $Skin->dark;
$th->find('a, label')->find('&:hover, &:active')->css(array(
  'color' => $Skin->navHlFg,
  'border-color' => $Skin->navHlFg
));
$Css('table tbody tr:hover td')->backgroundColor = $Skin->tableHlBg;

echo $Css;