<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\Payloads\Payload;
use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\Header\Part\AddressPart;
use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message\MimePart;

class LoggedMailPayload extends Payload
{
    /** @var string */
    protected $html = '';

    /** @var array */
    protected $from;

    /** @var string|null */
    protected $subject;

    /** @var array */
    protected $to;

    /** @var array */
    protected $cc;

    /** @var array */
    protected $bcc;

    /** @var array */
    protected $attachments;

    public static function forLoggedMail(string $loggedMail): self
    {
        $parser = new MailMimeParser();

        $message = $parser->parse($loggedMail, true);

        // get the part in $loggedMail that starts with <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0

        $content = self::getMailContent($loggedMail, $message);
        $attachments = self::getMailAttachments($message);

        return new self(
            $content,
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::FROM)),
            $message->getHeaderValue(HeaderConsts::SUBJECT),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::TO)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::CC)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::BCC)),
            $attachments,
        );
    }

    public function __construct(
        string $html,
        array $from = [],
        ?string $subject = null,
        array $to = [],
        array $cc = [],
        array $bcc = [],
        array $attachments = []
    ) {
        $this->html = $html;
        $this->from = $from;
        $this->subject = $subject;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->attachments = $attachments;
    }

    protected static function getMailContent(string $loggedMail, IMessage $message): string
    {
        $startOfHtml = strpos($loggedMail, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0', true);

        if (! $startOfHtml) {
            return $message->getContent() ?? $message->getHtmlContent() ?? '';
        }

        return substr($loggedMail, $startOfHtml) ?? '';
    }

    protected static function getMailAttachments(IMessage $message): array
    {
        return collect($message->getAllAttachmentParts())
            ->map(function (MimePart $attachmentPart) {
                return [
                    'filename' => $attachmentPart->getFilename(),
                    'content_id' => $attachmentPart->getContentId(),
                    'content_type' => $attachmentPart->getContentType(),
                    'content' => base64_encode($attachmentPart->getContent()),
                ];
            })->toArray();
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        return [
            'html' => $this->sanitizeHtml($this->html),
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'attachments' => $this->attachments,
        ];
    }

    protected function sanitizeHtml(string $html): string
    {
        $needle = 'Content-Type: text/html; charset=utf-8 Content-Transfer-Encoding: quoted-printable';

        if (strpos($html, $needle) !== false) {
            $html = substr($html, strpos($html, $needle));
        }

        return $html;
    }

    protected static function convertHeaderToPersons(?AddressHeader $header): array
    {
        if ($header === null) {
            return [];
        }

        return array_map(
            function (AddressPart $address) {
                return [
                    'name' => $address->getName(),
                    'email' => $address->getEmail(),
                ];
            },
            $header->getAddresses()
        );
    }
}
