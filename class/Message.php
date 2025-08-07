<?php
declare(strict_types=1);

namespace XoopsModules\Pm;

use XoopsObject;

/**
 * Class Message
 * @package XoopsModules\Pm
 */
class Message extends XoopsObject
{
    /**
     * Message constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('msg_id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('msg_image', \XOBJ_DTYPE_OTHER, 'icon1.gif', false, 100);
        $this->initVar('subject', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('from_userid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('to_userid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('msg_time', \XOBJ_DTYPE_INT, time(), false);
        $this->initVar('msg_text', \XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('read_msg', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('from_delete', \XOBJ_DTYPE_INT, 1, false);
        $this->initVar('to_delete', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('from_save', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('to_save', \XOBJ_DTYPE_INT, 0, false);
    }
}
