<?php

namespace Potager\Mailer;

use Potager\App;
use Potager\Mailer\Transports\SmtpTransport;

class MailManager
{
    protected array $transports = [];

    public function use(string $driver)
    {
        if (!App::useConfig()->get("mail.drivers.$driver")) {
            throw new \InvalidArgumentException("Mail driver [{$driver}] is not configured.");
        }

        if (!isset($this->transports[$driver])) {
            $this->transports[$driver] = $this->createTransport($driver);
        }

        return new Mailer($this->transports[$driver]);
    }

    public function send(\Closure $callback)
    {
        $default = App::useConfig()->get('mail.default');
        $this->use($default)->send($callback);
    }

    protected function createTransport($driver)
    {
        $driverConfig = App::useConfig()->get("mail.drivers.$driver");

        return match ($driver) {
            'smtp' => new SmtpTransport($driverConfig),
            default => throw new \InvalidArgumentException("Unspported mail driver [{$driver}].")
        };
    }

}