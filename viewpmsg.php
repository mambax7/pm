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

use Xmf\Request;

include_once dirname(dirname(__DIR__)) . '/mainfile.php';

include_once __DIR__ . '/header.php';

if (!is_object($GLOBALS['xoopsUser'])) {
    redirect_header(XOOPS_URL, 3, _NOPERM);
}
$xoopsConfig['module_cache']  = 0; //disable caching since the URL will be the same, but content different from one user to another
$GLOBALS['xoopsOption']['template_main'] = 'pm_viewpmsg.tpl';
include $GLOBALS['xoops']->path('header.php');

$valid_op_requests = array('out', 'save', 'in');
$_REQUEST['op']    = in_array(Request::getCmd('op', ''), $valid_op_requests) ? Request::getCmd('op', '') : 'in';

$start      = Request::getInt('start', 0, 'GET');
/** @var PmMessageHandler $pmHandler */
/** @var Xmf\Module\Helper $moduleHelper */
$pmHandler = $moduleHelper->getHandler('message');

$temp = Request::getString('delete_messages', null, 'POST');
if ('' != Request::getString('delete_messages', '', 'POST') && (isset($_POST['msg_id']) || isset($_POST['msg_ids']))) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $GLOBALS['xoopsTpl']->assign('errormsg', implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
    } elseif (empty(Request::getInt('ok', 0, 'POST'))) {
        xoops_confirm(array(
                          'ok'              => 1,
                          'delete_messages' => 1,
                          'op'              => Request::getCmd('op', ''),
                          'msg_ids'         => json_encode(array_map('intval', $_POST['msg_id']))), Request::getString('REQUEST_URI', '', 'SERVER'), _PM_SURE_TO_DELETE);
        include $GLOBALS['xoops']->path('footer.php');
        exit();
    } else {
        $clean_msg_id = json_decode($_POST['msg_ids'], true, 2);
        if (!empty($clean_msg_id)) {
            $clean_msg_id = array_map('intval', $clean_msg_id);
        }
        $size = count($clean_msg_id);
        $msg  =& $clean_msg_id;
        for ($i = 0; $i < $size; ++$i) {
            $pm = $pmHandler->get($msg[$i]);
            if ($pm->getVar('to_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                $pmHandler->setTodelete($pm);
            } elseif ($pm->getVar('from_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                $pmHandler->setFromdelete($pm);
            }
            unset($pm);
        }
        $GLOBALS['xoopsTpl']->assign('msg', _PM_DELETED);
    }
}
if ('' != Request::getString('move_messages', '', 'POST') && isset($_POST['msg_id'])) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $GLOBALS['xoopsTpl']->assign('errormsg', implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
    } else {
        $size = count($_POST['msg_id']);
        $msg  = $_POST['msg_id'];
        if ('save' === Request::getCmd('op', '', 'POST')) {
            for ($i = 0; $i < $size; ++$i) {
                $pm = $pmHandler->get($msg[$i]);
                if ($pm->getVar('to_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    $pmHandler->setTosave($pm, 0);
                } elseif ($pm->getVar('from_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    $pmHandler->setFromsave($pm, 0);
                }
                unset($pm);
            }
        } else {
            if (!$GLOBALS['xoopsUser']->isAdmin()) {
                $total_save = $pmHandler->getSavecount();
                $size       = min($size, $GLOBALS['xoopsModuleConfig']['max_save'] - $total_save);
            }
            for ($i = 0; $i < $size; ++$i) {
                $pm = $pmHandler->get($msg[$i]);
                if (Request::getCmd('op', '', 'POST') === 'in') {
                    $pmHandler->setTosave($pm);
                } elseif (Request::getCmd('op', '', 'POST') === 'out') {
                    $pmHandler->setFromsave($pm);
                }
                unset($pm);
            }
        }
        if (Request::getCmd('op', '', 'POST') === 'save') {
            $GLOBALS['xoopsTpl']->assign('msg', _PM_UNSAVED);
        } elseif (isset($total_save) && !$GLOBALS['xoopsUser']->isAdmin()) {
            $GLOBALS['xoopsTpl']->assign('msg', sprintf(_PM_SAVED_PART, $GLOBALS['xoopsModuleConfig']['max_save'], $i));
        } else {
            $GLOBALS['xoopsTpl']->assign('msg', _PM_SAVED_ALL);
        }
    }
}
if ('' !== Request::getString('empty_messages', '', 'POST')) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $GLOBALS['xoopsTpl']->assign('errormsg', implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
    } elseif (empty(Request::getInt('ok', 0))) {
        xoops_confirm(array('ok' => 1, 'empty_messages' => 1, 'op' => Request::getCmd('op', '')), Request::getString('REQUEST_URI', '', 'SERVER'), _PM_RUSUREEMPTY);
        include $GLOBALS['xoops']->path('footer.php');
        exit();
    } else {
        if (Request::getCmd('op', '', 'POST') === 'save') {
            $crit_to = new CriteriaCompo(new Criteria('to_delete', 0));
            $crit_to->add(new Criteria('to_save', 1));
            $crit_to->add(new Criteria('to_userid', $GLOBALS['xoopsUser']->getVar('uid')));
            $crit_from = new CriteriaCompo(new Criteria('from_delete', 0));
            $crit_from->add(new Criteria('from_save', 1));
            $crit_from->add(new Criteria('from_userid', $GLOBALS['xoopsUser']->getVar('uid')));
            $criteria = new CriteriaCompo($crit_to);
            $criteria->add($crit_from, 'OR');
        } elseif (Request::getCmd('op', '', 'POST') === 'out') {
            $criteria = new CriteriaCompo(new Criteria('from_delete', 0));
            $criteria->add(new Criteria('from_userid', $GLOBALS['xoopsUser']->getVar('uid')));
            $criteria->add(new Criteria('from_save', 0));
        } else {
            $criteria = new CriteriaCompo(new Criteria('to_delete', 0));
            $criteria->add(new Criteria('to_userid', $GLOBALS['xoopsUser']->getVar('uid')));
            $criteria->add(new Criteria('to_save', 0));
        }
        /*
         * The following method has critical scalability problem !
         * deleteAll method should be used instead
         */
        $pms = $pmHandler->getObjects($criteria);
        unset($criteria);
        if (count($pms) > 0) {
            foreach (array_keys($pms) as $i) {
                if ($pms[$i]->getVar('to_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    if (Request::getCmd('op', '', 'POST') === 'save') {
                        $pmHandler->setTosave($pms[$i], 0);
                    } elseif (Request::getCmd('op', '', 'POST') === 'in') {
                        $pmHandler->setTodelete($pms[$i]);
                    }
                }
                if ($pms[$i]->getVar('from_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    if (Request::getCmd('op', '', 'POST') === 'save') {
                        $pmHandler->setFromsave($pms[$i], 0);
                    } elseif (Request::getCmd('op', '', 'POST') === 'out') {
                        $pmHandler->setFromdelete($pms[$i]);
                    }
                }
            }
        }
        $GLOBALS['xoopsTpl']->assign('msg', _PM_EMPTIED);
    }
}

if (Request::getCmd('op', '') === 'out') {
    $criteria = new CriteriaCompo(new Criteria('from_delete', 0));
    $criteria->add(new Criteria('from_userid', $GLOBALS['xoopsUser']->getVar('uid')));
    $criteria->add(new Criteria('from_save', 0));
} elseif (Request::getCmd('op', '') === 'save') {
    $crit_to = new CriteriaCompo(new Criteria('to_delete', 0));
    $crit_to->add(new Criteria('to_save', 1));
    $crit_to->add(new Criteria('to_userid', $GLOBALS['xoopsUser']->getVar('uid')));
    $crit_from = new CriteriaCompo(new Criteria('from_delete', 0));
    $crit_from->add(new Criteria('from_save', 1));
    $crit_from->add(new Criteria('from_userid', $GLOBALS['xoopsUser']->getVar('uid')));
    $criteria = new CriteriaCompo($crit_to);
    $criteria->add($crit_from, 'OR');
} else {
    $criteria = new CriteriaCompo(new Criteria('to_delete', 0));
    $criteria->add(new Criteria('to_userid', $GLOBALS['xoopsUser']->getVar('uid')));
    $criteria->add(new Criteria('to_save', 0));
}
$total_messages = $pmHandler->getCount($criteria);
$criteria->setStart($start);
$criteria->setLimit($GLOBALS['xoopsModuleConfig']['perpage']);
$criteria->setSort('msg_time');
$criteria->setOrder('DESC');
$pm_arr = $pmHandler->getAll($criteria, null, false, false);
unset($criteria);

$GLOBALS['xoopsTpl']->assign('total_messages', $total_messages);
$GLOBALS['xoopsTpl']->assign('op', Request::getCmd('op', ''));

if ($total_messages > $GLOBALS['xoopsModuleConfig']['perpage']) {
    include $GLOBALS['xoops']->path('class/pagenav.php');
    $nav = new XoopsPageNav($total_messages, $GLOBALS['xoopsModuleConfig']['perpage'], $start, 'start', 'op=' . htmlspecialchars(Request::getCmd('op', '')));
    $GLOBALS['xoopsTpl']->assign('pagenav', $nav->renderNav(4));
}

$GLOBALS['xoopsTpl']->assign('display', $total_messages > 0);
$GLOBALS['xoopsTpl']->assign('anonymous', $xoopsConfig['anonymous']);
$uids = array();
if (count($pm_arr) > 0) {
    foreach (array_keys($pm_arr) as $i) {
        if (Request::getCmd('op', '') === 'out') {
            $uids[] = $pm_arr[$i]['to_userid'];
        } else {
            $uids[] = $pm_arr[$i]['from_userid'];
        }
    }
    /** @var XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    $senders        = $memberHandler->getUserList(new Criteria('uid', '(' . implode(', ', array_unique($uids)) . ')', 'IN'));
    foreach (array_keys($pm_arr) as $i) {
        $message              = $pm_arr[$i];
        $message['msg_image'] = htmlspecialchars($message['msg_image'], ENT_QUOTES);
        $message['msg_time']  = formatTimestamp($message['msg_time']);
        if (Request::getCmd('op', '') === 'out') {
            $message['postername'] = $senders[$pm_arr[$i]['to_userid']];
            $message['posteruid']  = $pm_arr[$i]['to_userid'];
        } else {
            $message['postername'] = $senders[$pm_arr[$i]['from_userid']];
            $message['posteruid']  = $pm_arr[$i]['from_userid'];
        }
        $message['msg_no'] = $i;
        $GLOBALS['xoopsTpl']->append('messages', $message);
    }
}

include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$send_button = new XoopsFormButton('', 'send', _PM_SEND);
$send_button->setExtra("onclick='javascript:openWithSelfMain(\"" . XOOPS_URL . "/modules/pm/pmlite.php?send=1\", \"pmlite\", 550, 450);'");
$delete_button = new XoopsFormButton('', 'delete_messages', _PM_DELETE, 'submit');
$move_button   = new XoopsFormButton('', 'move_messages', (Request::getCmd('op', '') === 'save') ? _PM_UNSAVE : _PM_TOSAVE, 'submit');
$empty_button  = new XoopsFormButton('', 'empty_messages', _PM_EMPTY, 'submit');

$pmform = new XoopsForm('', 'pmform', 'viewpmsg.php', 'post', true);
$pmform->addElement($send_button);
$pmform->addElement($move_button);
$pmform->addElement($delete_button);
$pmform->addElement($empty_button);
$pmform->addElement(new XoopsFormHidden('op', Request::getCmd('op', '')));
$pmform->assign($GLOBALS['xoopsTpl']);

include $GLOBALS['xoops']->path('footer.php');
