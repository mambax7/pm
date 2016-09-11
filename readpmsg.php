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

if (!is_object($GLOBALS['xoopsUser'])) {
    redirect_header(XOOPS_URL, 3, _NOPERM);
}
$valid_op_requests = array('out', 'save', 'in');
$_REQUEST['op']    = in_array(Request::getCmd('op', ''), $valid_op_requests) ? Request::getCmd('op', '') : 'in';
$msg_id            = Request::getInt('msg_id', 0);
/** @var PmMessageHandler $pmHandler */
$pmHandler        = xoops_getModuleHandler('message');
$pm                = null;
if ($msg_id > 0) {
    $pm = $pmHandler->get($msg_id);
}

if (is_object($pm) && ($pm->getVar('from_userid') != $GLOBALS['xoopsUser']->getVar('uid')) && ($pm->getVar('to_userid') != $GLOBALS['xoopsUser']->getVar('uid'))) {
    redirect_header(XOOPS_URL . '/modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . '/index.php', 2, _NOPERM);
}

if (is_object($pm) && !empty(Request::getString('action', '', 'POST'))) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        echo implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        exit();
    }
    $res = false;
    if (!empty(Request::getString('email_message', '', 'POST'))) {
        $res = $pmHandler->sendEmail($pm, $GLOBALS['xoopsUser']);
    } elseif (!empty(Request::getString('move_message', '', 'POST')) && Request::getCmd('op', '') !== 'save' && !$GLOBALS['xoopsUser']->isAdmin() && $pmHandler->getSavecount() >= $GLOBALS['xoopsModuleConfig']['max_save']) {
        $res_message = sprintf(_PM_SAVED_PART, $GLOBALS['xoopsModuleConfig']['max_save'], 0);
    } else {
        switch (Request::getCmd('op', '')) {
            case 'out':
                if ($pm->getVar('from_userid') != $GLOBALS['xoopsUser']->getVar('uid')) {
                    break;
                }
                if (!empty(Request::getString('delete_message', '', 'POST'))) {
                    $res = $pmHandler->setFromdelete($pm);
                } elseif (!empty(Request::getString('move_message', '', 'POST'))) {
                    $res = $pmHandler->setFromsave($pm);
                }
                break;
            case 'save':
                if ($pm->getVar('to_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    if (!empty(Request::getString('delete_message', '', 'POST'))) {
                        $res1 = $pmHandler->setTodelete($pm);
                        $res1 = $res1 ? $pmHandler->setTosave($pm, 0) : false;
                    } elseif (!empty(Request::getString('move_message', '', 'POST'))) {
                        $res1 = $pmHandler->setTosave($pm, 0);
                    }
                }
                if ($pm->getVar('from_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
                    if (!empty(Request::getString('delete_message', '', 'POST'))) {
                        $res2 = $pmHandler->setFromDelete($pm);
                        $res2 = $res2 ? $pmHandler->setFromsave($pm, 0) : false;
                    } elseif (!empty(Request::getString('move_message', '', 'POST'))) {
                        $res2 = $pmHandler->setFromsave($pm, 0);
                    }
                }
                $res = $res1 && $res2;
                break;

            case 'in':
            default:
                if ($pm->getVar('to_userid') != $GLOBALS['xoopsUser']->getVar('uid')) {
                    break;
                }
                if (!empty(Request::getString('delete_message', '', 'POST'))) {
                    $res = $pmHandler->setTodelete($pm);
                } elseif (!empty(Request::getString('move_message', '', 'POST'))) {
                    $res = $pmHandler->setTosave($pm);
                }
                break;
        }
    }
    $res_message = isset($res_message) ? $res_message : ($res ? _PM_ACTION_DONE : _PM_ACTION_ERROR);
    redirect_header('viewpmsg.php?op=' . htmlspecialchars(Request::getCmd('op', '')), 2, $res_message);
}
$start                        = Request::getInt('start', 0, 'GET');
$total_messages               = Request::getInt('total_messages', 0, 'GET');
$GLOBALS['xoopsOption']['template_main'] = 'pm_readpmsg.tpl';
include $GLOBALS['xoops']->path('header.php');

if (!is_object($pm)) {
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

    $criteria->setLimit(1);
    $criteria->setStart($start);
    $criteria->setSort('msg_time');
    $criteria->setOrder('DESC');
    list($pm) = $pmHandler->getObjects($criteria);
}

include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

$pmform = new XoopsForm('', 'pmform', 'readpmsg.php', 'post', true);
if (is_object($pm) && !empty($pm)) {
    if ($pm->getVar('from_userid') != $GLOBALS['xoopsUser']->getVar('uid')) {
        $reply_button = new XoopsFormButton('', 'send', _PM_REPLY);
        $reply_button->setExtra("onclick='javascript:openWithSelfMain(\"" . XOOPS_URL . '/modules/pm/pmlite.php?reply=1&msg_id=' . $pm->getVar('msg_id') . "\", \"pmlite\", 565,500);'");
        $pmform->addElement($reply_button);
    }
    $pmform->addElement(new XoopsFormButton('', 'delete_message', _PM_DELETE, 'submit'));
    $pmform->addElement(new XoopsFormButton('', 'move_message', (Request::getCmd('op', '') === 'save') ? _PM_UNSAVE : _PM_TOSAVE, 'submit'));
    $pmform->addElement(new XoopsFormButton('', 'email_message', _PM_EMAIL, 'submit'));
    $pmform->addElement(new XoopsFormHidden('msg_id', $pm->getVar('msg_id')));
    $pmform->addElement(new XoopsFormHidden('op', Request::getCmd('op', '')));
    $pmform->addElement(new XoopsFormHidden('action', 1));
    $pmform->assign($GLOBALS['xoopsTpl']);

    if ($pm->getVar('from_userid') == $GLOBALS['xoopsUser']->getVar('uid')) {
        $poster = new XoopsUser($pm->getVar('to_userid'));
    } else {
        $poster = new XoopsUser($pm->getVar('from_userid'));
    }
    if (!is_object($poster)) {
        $GLOBALS['xoopsTpl']->assign('poster', false);
        $GLOBALS['xoopsTpl']->assign('anonymous', $xoopsConfig['anonymous']);
    } else {
        $GLOBALS['xoopsTpl']->assign('poster', $poster);
    }

    if ($pm->getVar('to_userid') == $GLOBALS['xoopsUser']->getVar('uid') && $pm->getVar('read_msg') == 0) {
        $pmHandler->setRead($pm);
    }

    $message              = $pm->getValues();
    $message['msg_time']  = formatTimestamp($pm->getVar('msg_time'));
    $message['msg_image'] = htmlspecialchars($message['msg_image'], ENT_QUOTES);
}
$GLOBALS['xoopsTpl']->assign('message', $message);
$GLOBALS['xoopsTpl']->assign('op', Request::getCmd('op', '', 'POST'));
$GLOBALS['xoopsTpl']->assign('previous', $start - 1);
$GLOBALS['xoopsTpl']->assign('next', $start + 1);
$GLOBALS['xoopsTpl']->assign('total_messages', $total_messages);

include $GLOBALS['xoops']->path('footer.php');
