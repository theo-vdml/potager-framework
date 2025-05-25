<?php

namespace Potager\Mailer\Transports;

use Potager\Mailer\MailerException;
use Potager\Mailer\Message;
use Potager\Mailer\TransportInterface;
use Exception;

class SmtpTransport implements TransportInterface
{

    protected string $host;
    protected int $port;
    protected ?string $username;
    protected ?string $password;
    protected ?string $encryption;
    protected $socket;

    public function __construct(array $config)
    {
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? 1025;
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->encryption = $config['encryption'] ?? null;
    }

    public function send(Message $message): void
    {
        $message->verify();

        // 1. Se connecter au serveur
        $this->connect();

        // 2. AUTHENTICATE
        if ($this->username !== null && $this->password !== null) {
            $this->sendCommand('AUTH LOGIN');
            $this->expect(334);

            $this->sendCommand(base64_encode($this->username));
            $this->expect(334);

            $this->sendCommand(base64_encode($this->password));
            $this->expect(235);
        }


        // 3. MAIL FROM
        $this->sendCommand("MAIL FROM:<{$message->from['email']}>");
        $this->expect(250);

        // 4. RCPT TO
        foreach (array_merge($message->to, $message->cc ?? [], $message->bcc ?? []) as $recipient) {
            $this->sendCommand("RCPT TO:<{$recipient['email']}>");
            $this->expect(250);
        }
        ;

        // 5.DATA
        $this->sendCommand("DATA");
        $this->readResponse();

        // 6. Construire les headers du mail
        $headers = [];
        $headers[] = "From: " . $this->formatAddresses([$message->from]);
        $headers[] = "To: " . $this->formatAddresses($message->to);

        if ($message->cc) {
            $headers[] = "Cc: " . $this->formatAddresses($message->cc);
        }

        if ($message->bcc) {
            $headers[] = "Bcc: " . $this->formatAddresses($message->bcc);
        }

        $headers[] = "Subject: {$message->subject}";
        $headers[] = "MIME-Version: 1.0";

        if ($message->html) {
            $boundary = uniqid();
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        } else {
            $headers[] = "Content-Type: text/plain; charset=\"utf-8\"";
        }

        // 7. Construire le body du mail
        $body = "";

        if ($message->html) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n\r\n";
            $textContent = $message->text ?? strip_tags($message->html);
            $body .= $textContent;

            $body .= "\r\n\r\n";

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=\"utf-8\"\r\n\r\n";
            $body .= $message->html;
            $body .= "\r\n\r\n";

            $body .= "--{$boundary}--\r\n";
        } else {
            $body .= $message->text;
        }

        // 8. Envoyer headers + body + fin
        $this->sendCommand(implode("\r\n", $headers) . "\r\n\r\n" . $body);
        $this->sendCommand('.');

        // 9. Lire la réponse de confirmation
        $this->readResponse();

        // 10. QUIT proprement
        $this->sendCommand("QUIT");
        $this->readResponse();
    }

    protected function connect(): bool
    {
        $protocol = ($this->encryption === 'ssl') ? 'ssl://' : '';
        $this->socket = @fsockopen("{$protocol}{$this->host}", $this->port, $errno, $errstr, 10);

        if (!$this->socket) {
            throw new MailerException("Failed to connect to SMTP server : {$errstr} ({$errno})");
        }

        $this->expect(220);

        $this->sendCommand('EHLO localhost');
        $capabilities = $this->expect(250);

        if ($this->encryption === 'tls') {
            if (strpos($capabilities, 'STARTTLS') === false) {
                throw new MailerException("Server does not support STARTTLS.");
            }

            $this->sendCommand('STARTTLS');
            $this->expect(220);

            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new MailerException("Failed to start TLS encryption.");
            }

            // Refaire EHLO après le chiffrement TLS
            $this->sendCommand('EHLO localhost');
            $this->expect(250);
        }

        return true;

    }

    protected function sendCommand(string $command): void
    {
        fwrite($this->socket, "{$command}\r\n");
    }


    protected function readResponse(): string
    {
        $data = '';
        while ($line = fgets($this->socket, 515)) {
            $data .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $data;
    }

    protected function expect(int $code)
    {
        $response = $this->readResponse();
        if ((int) substr($response, 0, 3) !== $code)
            throw new MailerException("SMTP Error: Expected {$code}, got {$response}");
        return $response;
    }

    protected function formatAddresses(array $addresses)
    {
        return implode(', ', array_map(function ($address) {
            if (isset($address['name']) && $address['name']) {
                return "{$address['name']} <{$address['email']}>";
            }
            return "<{$address['email']}>";
        }, $addresses));
    }

}