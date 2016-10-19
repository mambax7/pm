<?php
/**
 * Private message module
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
 * @author              Jan Pedersen
 * @author              Taiwen Jiang <phppp@users.sourceforge.net>
 */

/**
 * This is a temporary solution for merging XOOPS 2.0 and 2.2 series
 * A thorough solution will be available in XOOPS 3.0
 *
 */

$moduleDirName = basename(__DIR__);
// ------------------- Informations ------------------- //
$modversion = array(
    'name'                => _PM_MI_NAME,
    'description'         => _PM_MI_DESC,
    'version'             => 1.12,
    'module_status'       => 'Beta 1',
    'release_date'        => '2016/10/18',
    //yyyy/mm/dd
    //    'release'             => '2015-04-04',
    'official'            => 1,
    //1 indicates supported by XOOPS Dev Team, 0 means 3rd party supported
    'author'              => 'Jan Pedersen, Taiwen Jiang',
    'author_mail'         => 'author-email',
    'author_website_url'  => 'http://xoops.org',
    'author_website_name' => 'XOOPS',
    'credits'             => 'The XOOPS Project, Wanikoo, Mamba, Geekwright',
    'license'             => 'GPL 2.0 or later',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html/',
    'help'                => 'page=help',
    'release_info'        => 'Changelog',
    'release_file'        => XOOPS_URL . "/modules/{$moduleDirName}/docs/changelog file",
    'manual'              => 'link to manual file',
    'manual_file'         => XOOPS_URL . "/modules/{$moduleDirName}/docs/install.txt",
    'min_php'             => '5.5',
    'min_xoops'           => '2.5.8',
    'min_admin'           => '1.2',
    'min_db'              => array('mysql' => '5.1'),
    // images
    'image'               => 'assets/images/logo.png',
    'dirname'             => $moduleDirName,
    'demo_site_url'       => 'http://www.xoops.org',
    'demo_site_name'      => 'XOOPS Demo Site',
    'support_url'         => 'http://xoops.org/modules/newbb/viewforum.php?forum=28/',
    'support_name'        => 'Support Forum',
    'module_website_url'  => 'www.xoops.org',
    'module_website_name' => 'XOOPS Project',

    // paypal
    //    'paypal' => array(
    //        'business' => 'XXX@email.com',
    //        'item_name' => 'Donation : ' . _AM_MODULE_DESC,
    //        'amount' => 0,
    //        'currency_code' => 'USD'
    //    ),

    // Admin system menu
    'system_menu'         => 1,
    // Admin menu
    'hasAdmin'            => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // Main menu
    'hasMain'             => 1,
    //    'sub'                 => array(
    //        array('name' => _MI_FM_SUB_SMNAME1, 'url' => 'movies.php'),
    //        array('name' => _MI_FM_SUB_SMNAME2, 'url' => 'clips.php')),

    //Search & Comments
//    'hasSearch'           => 1,
//    'search'              => array(
//        'file' => 'include/search.inc.php',
//        'func' => 'tdmpicture_search'
//    ),
//    'hasComments'         => 1,
//    'comments'            => array(
//        'pageName'     => 'viewfile.php',
//        'itemName'     => 'st',
//        'callbackFile' => 'include/comment_functions.php',
//        'callback'     => array(
//            'approve' => 'picture_comments_approve',
//            'update'  => 'picture_comments_update'
//        ),
//    ),
    // Notification
//    'hasNotification'     => 0,
    // Install/Update
    'onInstall'           => 'include/install.php',
    'onUpdate'            => 'include/update.php'
    //  'onUninstall'         => 'include/onuninstall.php'

);



// ------------------- Templates ------------------- //

$modversion['templates'] = array(
    array('file' => 'pm_pmlite.tpl', 'description' => ''),
    array('file' => 'pm_readpmsg.tpl', 'description' => ''),
    array('file' => 'pm_viewpmsg.tpl', 'description' => ''),
);


// ------------------- Help files ------------------- //
$modversion['helpsection'] = array(
    array('name' => _MI_PM_OVERVIEW, 'link' => 'page=help'),
    array('name' => _MI_PM_DISCLAIMER, 'link' => 'page=disclaimer'),
    array('name' => _MI_PM_LICENSE, 'link' => 'page=license'),
    array('name' => _MI_PM_SUPPORT, 'link' => 'page=support'),
);

$modversion['config']   = array();
$modversion['config'][] = array(
    'name'        => 'perpage',
    'title'       => '_PM_MI_PERPAGE',
    'description' => '_PM_MI_PERPAGE_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 20);

$modversion['config'][] = array(
    'name'        => 'max_save',
    'title'       => '_PM_MI_MAXSAVE',
    'description' => '_PM_MI_MAXSAVE_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 10);

$modversion['config'][] = array(
    'name'        => 'prunesubject',
    'title'       => '_PM_MI_PRUNESUBJECT',
    'description' => '_PM_MI_PRUNESUBJECT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => _PM_MI_PRUNESUBJECTDEFAULT);

$modversion['config'][] = array(
    'name'        => 'prunemessage',
    'title'       => '_PM_MI_PRUNEMESSAGE',
    'description' => '_PM_MI_PRUNEMESSAGE_DESC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _PM_MI_PRUNEMESSAGEDEFAULT);
