<?php

namespace Potager\Mailer;

use Closure;

class Mailer
{

    public function __construct(protected TransportInterface $transport)
    {
        // 
    }

    public function send(Closure $callback): void
    {
        $message = new Message();
        $callback($message);
        $this->transport->send($message);
    }

}