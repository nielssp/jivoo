<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

use Jivoo\Jtk\JtkObject;

/**
 * A menu item. Menu items can be added to a {@see Menu}.
 * All menu items can be extended with custom properties, e.g.
 * label, icon, shortcut key, etc., using the magic getters and setters.
 */
abstract class MenuItem extends JtkObject {
}