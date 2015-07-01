<?php $this->extend('jivoo/jtk/layout/wide.html'); ?>

<?php $this->begin('shortcuts-menu'); ?>

<?php 
$menu = $Jtk->Menu;
$menu->appendAction('Index');
$menu->appendAction('Page 1');
$menu->appendAction('Page 2');
$sub = $menu->appendMenu('Submenu');
$sub->appendAction('Page 1');
$sub->appendAction('Page 2');
echo $menu();
?>

<?php $this->end(); ?>


<?php $this->begin('main-menu'); ?>

<?php 
$menu = $Jtk->Menu;
$jtk = $menu->appendMenu(tr('JTK'));
$jtk->appendAction(tr('Dashboard'))->setRoute('action:App::index')->setIcon('meter');
$jtk->appendAction(tr('Colors'))->setRoute('action:App::colors')->setIcon('paint-format');

$ui = $menu->appendMenu(tr('UI'));
$sub = $ui->appendMenu(tr('Elements'))->setIcon('newspaper');
$sub->appendAction(tr('Typography'))->setRoute('path:ui/elements/typography');
$sub->appendAction(tr('Buttons'))->setRoute('path:ui/elements/buttons');
$sub->appendAction(tr('Icons'))->setRoute('path:ui/elements/icons');
$sub->appendAction(tr('Blocks'))->setRoute('path:ui/elements/blocks');
$sub->appendAction(tr('Notifications'))->setRoute('path:ui/elements/notifications');
$sub->appendAction(tr('Modals'))->setRoute('path:ui/elements/modals');
$sub->appendAction(tr('Tooltips'))->setRoute('path:ui/elements/tooltips');

$sub = $ui->appendMenu(tr('Forms'))->setIcon('quill');
$sub->appendAction(tr('Elements'))->setRoute('path:ui/forms/elements');
$sub->appendAction(tr('Layout'))->setRoute('path:ui/forms/layout');

$sub = $ui->appendMenu(tr('Tables'))->setIcon('list');
$sub->appendAction(tr('Simple'))->setRoute('path:ui/tables/simple');
$sub->appendAction(tr('Advanced'))->setRoute('path:ui/tables/advanced');

echo $menu();
?>

<?php $this->end(); ?>

<?php echo $this->block('content'); ?>
