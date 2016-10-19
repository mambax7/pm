<?php

use Xmf\Module\Admin;

/**
 * Private message
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       (c) 2000-2016 XOOPS Project (www.xoops.org)
 * @license             GNU GPL 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package             pm
 * @since               2.3.0
 * @author              Taiwen Jiang <phppp@users.sourceforge.net>
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

require_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';

$pathIcon32 = Admin::menuIconPath('');
$moduleDirName = basename(dirname(__DIR__));

$adminmenu[] = array(
    'title' => _PM_MI_INDEX,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png'
);

$adminmenu[] = array(
    'title' => _PM_MI_PRUNE,
    'link'  => 'admin/prune.php',
    'icon'  => $pathIcon32 . '/prune.png'
);

$adminmenu[] = array(
    'title' => _PM_MI_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png'
);
