<?php

namespace pkpudev\notification\notify;

use pkpudev\notification\ProgramHelper;
use pkpudev\notification\event\IppEvent;
use pkpudev\notification\recipient\ModelName;
use pkpudev\notification\recipient\RecipentMapper;
use pkpudev\notification\recipient\RecipientQuery;
use pkpudev\notification\transform\IppTransform;
use yii\swiftmailer\Message;

/**
 * @author Zein Miftah <zeinmiftah@gmail.com>
 */
class IppStatusNotify implements StatusNotifyInterface
{
    /** @var IppTransform $transform */
    public $transform;
    /** @var IppEvent $event */
    public $event;
    /** @var RecipientMapper $recipientMapper */
    public $recipientMapper;
    /** @var Message $_message */
    private $_message;

    public function __construct(IppTransform $transform, IppEvent $event)
    {
        $this->transform = $transform;
        $this->event = $event;

        $model = $transform->getModel();
        $modelName = new ModelName('Ipp');
        $query = new RecipientQuery($modelName, $model->company_id, $model->branch_id);
        $this->recipientMapper = new RecipentMapper($transform, $query, $event);
    }
    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        if ($this->_message) {
            return $this->_message;
        }

        // Compose subject
        $params = $this->transform->getParams();
        $subject = sprintf("%s IPP#%s %s %s",
            ProgramHelper::titlePrefix($params->is_ramadhan, $params->year),
            $params->no,
            $params->title,
            $this->event->eventDesc
        );
        // Compose mail
        $this->_message = new Message;
        $this->_message->to = $this->recipientMapper->getToAddress();
        $this->_message->cc = $this->recipientMapper->getCcAddress();
        $this->_message->subject = $subject;
        return $this->_message;
    }
    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return $this->transform->getParams();
    }
    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->event->getEventFile();
    }
}