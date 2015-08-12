<?php

namespace Jivoo\Jtk\Menu;

class MenuTest extends \Jivoo\Test {

  protected function _before() {}

  protected function _after() {}

  public function testAppend() {
    $menu = new Menu('File');
    $menu[] = new MenuAction('New');
    $menu->appendAction('Open');
    $menu->appendMenu('Open Recent');
    $menu->appendSeparator();
    $menu->append(array(
      'save' => new MenuAction('Save'),
      new MenuSeparator(),
      'exit' => new MenuAction('Exit'),
    ));
    
    $this->assertEquals(7, count($menu));
    $this->assertEquals('Open Recent', $menu->itemAt(2)->label);
    $this->assertEquals(6, $menu->getOffset('exit'));
    $this->assertEquals('Save', $menu['save']->label);
    
    $menu->remove('something');
    $this->assertEquals(7, count($menu));
    
    unset($menu['save']);
    $this->assertEquals(6, count($menu));
    $this->assertNull($menu['save']);
    $this->assertFalse(isset($menu['save']));
    
    $menu->removeOffset(0);
    $this->assertEquals(5, count($menu));
    $this->assertEquals('Open', $menu->itemAt(0)->label);
    
    $menu['save'] = new MenuAction('Save As');
    $this->assertEquals('Save As', $menu['save']->label);
    
    $this->assertTrue($menu->getIterator() instanceof \Iterator);
  }
  
  public function testPrepend() {
    $menu = new Menu('Edit');
    $menu->prependMenu('Paste', 'paste');
    $menu->prependAction('Copy');
    $menu->prependAction('Cut');
    $menu->prependSeparator();
    $menu->prepend(array(
      'undo' => new MenuAction('Undo'),
      new MenuAction('Redo')
    ));
    
    $this->assertEquals(6, count($menu));
    $this->assertEquals('Redo', $menu->itemAt(1)->label);
    $this->assertEquals('Cut', $menu->itemAt(3)->label);
    $this->assertEquals(0, $menu->getOffset('undo'));
    $this->assertEquals(5, $menu->getOffset('paste'));
  }
  
  public function testInsert() {
    $menu = new Menu('Help');
    $menu->insertMenu(0, 'Help contents');
    $menu->insertSeparator(1);
    $menu->insertAction(2, 'Check for updates');
    $menu->insertSeparator(3);
    $menu->insertAction(4, 'About');
    
    $menu->insert(1, array(
      new MenuSeparator(),
      'install' => new MenuAction('Install new software'),
      new MenuAction('Installation details')
    ));
    
    $this->assertEquals(8, count($menu));
    $this->assertEquals('Help contents', $menu->itemAt(0)->label);
    $this->assertEquals('Installation details', $menu->itemAt(3)->label);
    $this->assertEquals('About', $menu->itemAt(7)->label);
    $this->assertEquals(2, $menu->getOffset('install'));
  }
}
