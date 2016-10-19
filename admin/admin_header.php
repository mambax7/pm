<?php

use Xmf\Language;
use Xmf\Module\Admin;
use Xmf\Module\Helper;

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

$path = dirname(dirname(dirname(__DIR__)));
include_once $path . '/mainfile.php';
include_once $path . '/include/cp_functions.php';
require_once $path . '/include/cp_header.php';

global $xoopsModule;

//$moduleDirName = $GLOBALS['xoopsModule']->getVar('dirname');
$moduleDirName = basename(dirname(__DIR__));
/** @var Xmf\Module\Helper $moduleHelper */
$moduleHelper  = Helper::getHelper($moduleDirName);

//if functions.php file exist
//require_once dirname(__DIR__) . '/include/functions.php';

$moduleHelper->loadLanguage('admin');
$moduleHelper->loadLanguage('modinfo');
$moduleHelper->loadLanguage('main');


$pathIcon16      = Admin::iconUrl('', 16);
$pathIcon32      = Admin::iconUrl('', 32);

//include_once $GLOBALS['xoops']->path($pathModuleAdmin . '/moduleadmin.php');

if ($GLOBALS['xoopsUser']) {
    /** @var XoopsGroupPermHandler $groupPermHandler */
    $groupPermHandler = xoops_getHandler('groupperm');
    if (!$groupPermHandler->checkRight('module_admin', $xoopsModule->getVar('mid'), $GLOBALS['xoopsUser']->getGroups())) {
        redirect_header(XOOPS_URL, 1, _NOPERM);
    }
} else {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);
}

if (!isset($xoopsTpl) || !is_object($xoopsTpl)) {
    include_once XOOPS_ROOT_PATH . '/class/template.php';
    $xoopsTpl = new XoopsTpl();
}

if (!isset($GLOBALS['xoopsTpl']) || !is_object($GLOBALS['xoopsTpl'])) {
    include_once XOOPS_ROOT_PATH . '/class/template.php';
    $GLOBALS['xoopsTpl'] = new XoopsTpl();
}
/** @var Xmf\Module\Admin $adminObject */
$adminObject = Admin::getInstance();
