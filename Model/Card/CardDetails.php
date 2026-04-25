<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Card;

class CardDetails
{
    const TYPE = 'type';
    const MASKED_CC = 'maskedCC';
    const EXPIRATION_DATE = 'expirationDate';

    const CARD_TYPES = [
        'discover' => 'DI',
        'diners club' => 'DN',
        'jcb' => 'JCB',
        'maestro-intl.' => 'MI',
        'mastercard' => 'MC',
        'ecmc' => 'MC',
        'unionpay' => 'UN',
        'visa' => 'VI',
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $mask;

    /**
     * @var string
     */
    private $expirationDate;

    public function __construct(
        string $type,
        string $mask,
        string $expirationDate
    ) {
        $this->type = $type;
        $this->mask = $mask;
        $this->expirationDate = $expirationDate;
    }

    public function toJson(): string
    {
        return (string) json_encode([
            self::TYPE => $this->formatCardType($this->type),
            self::MASKED_CC => $this->formatCardMask($this->mask),
            self::EXPIRATION_DATE => $this->formatExpirationDate($this->expirationDate),
        ]);
    }

    public function getTokenExpirationDate(): string
    {
        $dateChunk = str_split($this->expirationDate, 2);

        $expirationDate = new \DateTime($dateChunk[1] . $dateChunk[2] . '-' . $dateChunk[0] . '-01');
        $expirationDate->add(new \DateInterval('P1M'));

        return $expirationDate->format('Y-m-d 00:00:00');
    }

    private function formatCardType(string $type): string
    {
        return self::CARD_TYPES[$type] ?? $type;
    }

    private function formatCardMask(string $mask): string
    {
        return trim(str_replace('x', '', strtolower($mask)));
    }

    private function formatExpirationDate(string $date): string
    {
        $dateChunk = str_split($date, 2);

        return $dateChunk[0] . '/' . $dateChunk[1] . $dateChunk[2];
    }
}
