<?php

namespace Caxy\AppEngine\Bridge\Swiftmailer;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use google\appengine\api\mail\Message;
use Swift_Events_EventListener;
use Swift_Mime_Message;

class AppEngineTransport implements \Swift_Transport
{
    /** The event dispatching layer */
    protected $_eventDispatcher;

    /**
     * Creates a new EsmtpTransport using the given I/O buffer.
     *
     * @param \Swift_Events_EventDispatcher $dispatcher
     */
    public function __construct(\Swift_Events_EventDispatcher $dispatcher)
    {
        $this->_eventDispatcher = $dispatcher;
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        // TODO: Implement isStarted() method.
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        // TODO: Implement stop() method.
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $swiftMessage
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $swiftMessage, &$failedRecipients = null)
    {
        try {
            $message = new Message(array(
                'sender' => $swiftMessage->getSender(),
                'replyto' => $swiftMessage->getReplyTo(),
                'to' => $swiftMessage->getTo(),
                'cc' => $swiftMessage->getCc(),
                'bcc' => $swiftMessage->getBcc(),
                'subject' => $swiftMessage->getSubject(),
                'header' => $swiftMessage->getHeaders(),
            ));

            $message->setTextBody($swiftMessage->getBody());

            foreach ($swiftMessage->getChildren() as $child) {
                /** @var \Swift_Mime_Headers_ParameterizedHeader $header */
                $header = $child->getHeaders()->get('Content-Disposition');
                $filename = $header->getParameter('filename');
                $data = $child->getBody();
                $contentId = $child->getId();

                $message->addAttachment($filename, $data, $contentId);
            }
            $message->send();
        } catch (InvalidArgumentException $e) {
            // ...
        }
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }
}
