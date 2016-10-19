<?php
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
 * @author              Jan Pedersen
 * @author              Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;

include_once __DIR__ . '/admin_header.php';
xoops_cp_header();

echo $adminObject->displayNavigation(basename(__FILE__));

$op         = Request::getString('op', 'form');

/** @var PmMessageHandler $pmHandler */
$pmHandler = $moduleHelper->getHandler('message');

switch ($op) {
    default:
    case 'form':
        $form = $pmHandler->getPruneForm();
        $form->display();
        break;

    case 'prune':
        $uids = $errormsg = array();
        $criteria = new CriteriaCompo();
        if (Request::hasVar(['after']['date']) &&  'YYYY/MM/DD' !== Request::getString(['after']['date'])) {
            $criteria->add(new Criteria('msg_time', strtotime(Request::getString(['after']['date'])) + Request::getInt(['after']['time']), '>'));
        }
        if (Request::hasVar(['before']['date']) && 'YYYY/MM/DD' !== Request::getString(['before']['date'])) {
            $criteria->add(new Criteria('msg_time', strtotime(Request::getString(['before']['date'])) + Request::getInt(['before']['time']), '<'));
        }
        if (1 == Request::getInt('onlyread', 0)) {
            $criteria->add(new Criteria('read_msg', 1));
        }
        if (0 == Request::getInt('includesave', 0)) {
            $savecriteria = new CriteriaCompo(new Criteria('to_save', 0));
            $savecriteria->add(new Criteria('from_save', 0));
            $criteria->add($savecriteria);
        }
        if (1 == Request::getInt('notifyusers', 0)) {
            $uids = array();
            $notifycriteria = $criteria;
            $notifycriteria->add(new Criteria('to_delete', 0));
            $notifycriteria->setGroupBy('to_userid');
            // Get array of uid => number of deleted messages
            $uids = $pmHandler->getCount($notifycriteria);
        }
        $deletedrows = $pmHandler->deleteAll($criteria);
        if ($deletedrows === false) {
            redirect_header('prune.php', 2, _PM_AM_ERRORWHILEPRUNING);
        }
        if (1 == Request::getInt('notifyusers', 0)) {
            $errors = false;
            foreach ($uids as $uid => $messagecount) {
                $pm = $pmHandler->create();
                $pm->setVar('subject', $GLOBALS['xoopsModuleConfig']['prunesubject']);
                $pm->setVar('msg_text', str_replace('{PM_COUNT}', $messagecount, $GLOBALS['xoopsModuleConfig']['prunemessage']));
                $pm->setVar('to_userid', $uid);
                $pm->setVar('from_userid', $GLOBALS['xoopsUser']->getVar('uid'));
                $pm->setVar('msg_time', time());
                if (!$pmHandler->insert($pm)) {
                    $errors     = true;
                    $errormsg[] = $pm->getHtmlErrors();
                }
                unset($pm);
            }
            if ($errors === true) {
                echo implode('<br>', $errormsg);
                xoops_cp_footer();
                exit();
            }
        }
        redirect_header('index.php', 2, sprintf(_PM_AM_MESSAGESPRUNED, $deletedrows));
        break;
}
include_once __DIR__ . '/admin_footer.php';
//xoops_cp_footer();
