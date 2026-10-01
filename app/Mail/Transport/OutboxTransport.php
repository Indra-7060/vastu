<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Local test inbox (MAIL_MAILER=outbox): instead of sending, every email is saved to
 * storage/app/mail-outbox and can be read at /dev/mailbox (local environment, admins only).
 */
class OutboxTransport extends AbstractTransport
{
    public const DIR = 'mail-outbox';

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $dir = storage_path('app/'.self::DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $id = now()->format('Ymd-His').'-'.substr(bin2hex(random_bytes(4)), 0, 8);
        $list = fn (array $addresses) => array_map(fn (Address $a) => $a->toString(), $addresses);

        file_put_contents($dir.'/'.$id.'.json', json_encode([
            'id' => $id,
            'date' => now()->toDateTimeString(),
            'subject' => (string) $email->getSubject(),
            'from' => $list($email->getFrom()),
            'to' => $list($email->getTo()),
            'cc' => $list($email->getCc()),
            'html' => (string) ($email->getHtmlBody() ?? ''),
            'text' => (string) ($email->getTextBody() ?? ''),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function __toString(): string
    {
        return 'outbox://local';
    }
}
