<?php
declare(strict_types=1);

namespace XoopsModules\Pm;

use XoopsPersistableObjectHandler;
use XoopsDatabase;
use XoopsUser;
use XoopsThemeForm;
use Criteria;
use CriteriaCompo;

/**
 * Class MessageHandler
 * @package XoopsModules\Pm
 */
class MessageHandler extends XoopsPersistableObjectHandler
{
    /**
     * @param null|XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'priv_msgs', Message::class, 'msg_id', 'subject');
    }

    /**
     * Mark a message as read
     *
     * @param Message $pm {@link Message} object
     * @param int     $val
     *
     * @return bool
     */
    public function setRead(Message $pm, int $val = 1): bool
    {
        return $this->updateAll('read_msg', $val, new Criteria('msg_id', $pm->getVar('msg_id')), true);
    }

    /**
     * Mark a message as from_delete = 1 or removes it if the recipient has also deleted it
     * @param  Message $pm {@link Message} object
     * @param  int     $val
     * @return bool
     */
    public function setFromdelete(Message $pm, int $val = 1): bool
    {
        if ($pm->getVar('to_delete') == 0) {
            return $this->updateAll('from_delete', $val, new Criteria('msg_id', $pm->getVar('msg_id')));
        }

        return parent::delete($pm);
    }

    /**
     * Mark a message as to_delete = 1 or removes it if the sender has also deleted it or sent by anonymous
     * @param  Message $pm {@link Message} object
     * @param  int     $val
     * @return bool
     */
    public function setTodelete(Message $pm, int $val = 1): bool
    {
        if ($pm->getVar('from_delete') == 0 || $pm->getVar('from_userid') == 0) {
            return $this->updateAll('to_delete', $val, new Criteria('msg_id', $pm->getVar('msg_id')));
        }

        return parent::delete($pm);
    }

    /**
     * Mark a message as from_save = 1
     * @param  Message $pm {@link Message} object
     * @param  int     $val
     * @return bool
     */
    public function setFromsave(Message $pm, int $val = 1): bool
    {
        return $this->updateAll('from_save', $val, new Criteria('msg_id', $pm->getVar('msg_id')));
    }

    /**
     * Mark a message as to_save = 1
     * @param  Message $pm {@link Message} object
     * @param  int     $val
     * @return bool
     */
    public function setTosave(Message $pm, int $val = 1): bool
    {
        return $this->updateAll('to_save', $val, new Criteria('msg_id', $pm->getVar('msg_id')));
    }

    /**
     * get user's message count in savebox
     * @param  null|XoopsUser $user
     * @return int
     **/
    public function getSavecount(XoopsUser $user = null): int
    {
        if (!is_object($user)) {
            $user =& $GLOBALS['xoopsUser'];
        }
        $crit_to = new CriteriaCompo(new Criteria('to_delete', 0));
        $crit_to->add(new Criteria('to_save', 1));
        $crit_to->add(new Criteria('to_userid', $user->getVar('uid')));
        $crit_from = new CriteriaCompo(new Criteria('from_delete', 0));
        $crit_from->add(new Criteria('from_save', 1));
        $crit_from->add(new Criteria('from_userid', $user->getVar('uid')));
        $criteria = new CriteriaCompo($crit_to);
        $criteria->add($crit_from, 'OR');

        return $this->getCount($criteria);
    }

    /**
     * Send a message to user's email
     * @param  Message   $pm   {@link Message} object
     * @param  XoopsUser $user
     * @return bool
     **/
    public function sendEmail(Message $pm, XoopsUser $user): bool
    {
        global $xoopsConfig;

        $msg = sprintf(_PM_EMAIL_DESC, $user->getVar('uname'));
        $msg .= "\n\n";
        $msg .= formatTimestamp($pm->getVar('msg_time'));
        $msg .= "\n";
        $from = new XoopsUser($pm->getVar('from_userid'));
        $to   = new XoopsUser($pm->getVar('to_userid'));
        $msg .= sprintf(_PM_EMAIL_FROM, $from->getVar('uname') . ' (' . XOOPS_URL . '/userinfo.php?uid=' . $pm->getVar('from_userid') . ')');
        $msg .= "\n";
        $msg .= sprintf(_PM_EMAIL_TO, $to->getVar('uname') . ' (' . XOOPS_URL . '/userinfo.php?uid=' . $pm->getVar('to_userid') . ')');
        $msg .= "\n";
        $msg .= _PM_EMAIL_MESSAGE . ":\n";
        $msg .= "\n" . $pm->getVar('subject') . "\n";
        $msg .= "\n" . strip_tags(str_replace(array('<p>', '</p>', '<br>', '<br>'), "\n", $pm->getVar('msg_text'))) . "\n\n";
        $msg .= "--------------\n";
        $msg .= $xoopsConfig['sitename'] . ': ' . XOOPS_URL . "\n";

        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setToEmails($user->getVar('email'));
        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
        $xoopsMailer->setFromName($xoopsConfig['sitename']);
        $xoopsMailer->setSubject(sprintf(_PM_EMAIL_SUBJECT, $pm->getVar('subject')));
        $xoopsMailer->setBody($msg);

        return $xoopsMailer->send();
    }

    /**
     * Get {@link XoopsThemeForm} for setting prune criteria
     *
     * @return XoopsThemeForm
     **/
    public function getPruneForm(): XoopsThemeForm
    {
        include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
        $form = new \XoopsThemeForm(_PM_AM_PRUNE, 'form', 'prune.php', 'post', true);

        $form->addElement(new \XoopsFormDateTime(_PM_AM_PRUNEAFTER, 'after'));
        $form->addElement(new \XoopsFormDateTime(_PM_AM_PRUNEBEFORE, 'before'));
        $form->addElement(new \XoopsFormRadioYN(_PM_AM_ONLYREADMESSAGES, 'onlyread', 1));
        $form->addElement(new \XoopsFormRadioYN(_PM_AM_INCLUDESAVE, 'includesave', 0));
        $form->addElement(new \XoopsFormRadioYN(_PM_AM_NOTIFYUSERS, 'notifyusers', 0));

        $form->addElement(new \XoopsFormHidden('op', 'prune'));
        $form->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        return $form;
    }
}
