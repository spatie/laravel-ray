<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\Payloads\Payload;
use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\Header\Part\AddressPart;
use ZBateson\MailMimeParser\MailMimeParser;

class LoggedMailPayload extends Payload
{
    protected string $html = '';

    private array $from;

    private ?string $subject;

    private array $to;

    private array $cc;

    private array $bcc;

    public static function forLoggedMail(string $loggedMail): self
    {
        $parser = new MailMimeParser();

        $message = $parser->parse($loggedMail);

        return new self(
            $message->getContent(),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::FROM)),
            $message->getHeaderValue(HeaderConsts::SUBJECT),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::TO)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::CC)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::BCC)),
        );
    }

    public function __construct(
        string $html,
        array $from = [],
        ?string $subject = null,
        array $to = [],
        array $cc = [],
        array $bcc = []
    ) {
        $this->html = $html;
        $this->from = $from;
        $this->subject = $subject;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        return [
            'html' => $this->html,
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
        ];
    }

    protected static function convertHeaderToPersons(?AddressHeader $header): array
    {
        if ($header === null) {
            return [];
        }

        return array_map(
            fn (AddressPart $address) => [
                'name' => $address->getName(),
                'email' => $address->getEmail(),
            ],
            $header->getAddresses()
        );
    }
}
