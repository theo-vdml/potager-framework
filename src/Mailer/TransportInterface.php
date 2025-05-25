<?php

namespace Potager\Mailer;

use Potager\Mailer\Message;

interface TransportInterface
{
    public function send(Message $message): void;
}