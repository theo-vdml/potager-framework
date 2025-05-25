<?php

namespace Potager\Mailer;

use InvalidArgumentException;

class Message
{

    public ?array $from = null;
    public ?array $to = null;
    public string $subject = '';
    public ?array $cc = null;
    public ?array $bcc = null;
    public ?string $html = null;
    public ?string $text = null;

    public function verify()
    {
        if (!$this->from || !isset($this->from['email']) || empty($this->from['email']))
            throw new InvalidArgumentException('The "from" field is required.');
        if (!$this->to || empty($this->to))
            throw new InvalidArgumentException('At least one "to" recipient is required.');
        if (!$this->html && !$this->text)
            throw new InvalidArgumentException('At least one content field ("html" or "text") is required.');
    }

    public function from(string $email, ?string $name = null)
    {
        $this->from = ["email" => $email, "name" => $name];
        return $this;
    }

    public function to(mixed $rcpt, ?string $name = null)
    {
        $this->to = array_merge($this->to ?? [], $this->normalizeRecipients($rcpt, $name));
        return $this;
    }

    public function cc(mixed $rcpt, ?string $name = null)
    {
        $this->cc = array_merge($this->cc ?? [], $this->normalizeRecipients($rcpt, $name));
        return $this;
    }

    public function bcc(mixed $rcpt, ?string $name = null)
    {
        $this->bcc = array_merge($this->bcc ?? [], $this->normalizeRecipients($rcpt, $name));
        return $this;
    }

    protected function normalizeRecipients(mixed $rcpt, ?string $name = null): array
    {
        $result = [];

        if (is_array($rcpt)) {
            foreach ($rcpt as $item) {
                if (is_array($item) && count($item) === 2) {
                    $result[] = ["email" => $item[0], "name" => $item[1]];
                } else if (is_string($item)) {
                    $result[] = ["email" => $item, "name" => null];
                } else {
                    $type = gettype($item);
                    throw new InvalidArgumentException("Invalid recipient in list: expected string or array [email, name], got {$type}.");
                }
            }
        } else if (is_string($rcpt)) {
            $result[] = ["email" => $rcpt, "name" => $name];
        } else {
            $type = gettype($rcpt);
            throw new InvalidArgumentException("Invalid recipient type: expected string or array of recipients, got {$type}.");
        }

        return $result;
    }


    public function subject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function html(string $content)
    {
        $this->html = $content;
        return $this;
    }

    public function text(string $content)
    {
        $this->text = $content;
        return $this;
    }

}