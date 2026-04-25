<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Api;

use PayPro\Przelewy24\Api\Data\ApiPaymentMethodInterface;

class ApiPaymentMethod implements ApiPaymentMethodInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool
     */
    private $status;

    /**
     * @var string|null
     */
    private $imgUrl;

    /**
     * @var string|null
     */
    private $mobileImgUrl;

    /**
     * @var bool|null
     */
    private $isStandalone;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?bool $status = null,
        ?string $imgUrl = null,
        ?string $mobileImgUrl = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->status = (bool) $status;
        $this->imgUrl = $imgUrl;
        $this->mobileImgUrl = $mobileImgUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function setImgUrl(string $imgUrl): void
    {
        $this->imgUrl = $imgUrl;
    }

    public function getMobileImgUrl(): ?string
    {
        return $this->mobileImgUrl;
    }

    public function setMobileImgUrl(string $mobileImgUrl): void
    {
        $this->mobileImgUrl = $mobileImgUrl;
    }

    public function isStandalone(): bool
    {
        return (bool) $this->isStandalone;
    }

    public function setIsStandalone(bool $isStandalone): void
    {
        $this->isStandalone = $isStandalone;
    }
}
