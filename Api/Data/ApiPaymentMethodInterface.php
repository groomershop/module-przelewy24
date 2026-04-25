<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Api\Data;

interface ApiPaymentMethodInterface
{
    const ERATY_SCB_ID = 303;

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $status
     * @return void
     */
    public function setStatus(bool $status): void;

    /**
     * @return string|null
     */
    public function getImgUrl(): ?string;

    /**
     * @param string $imgUrl
     * @return void
     */
    public function setImgUrl(string $imgUrl): void;

    /**
     * @return string|null
     */
    public function getMobileImgUrl(): ?string;

    /**
     * @param string $mobileImgUrl
     * @return void
     */
    public function setMobileImgUrl(string $mobileImgUrl): void;

    /**
     * @return bool
     */
    public function isStandalone(): bool;

    /**
     * @param bool $isStandalone
     * @return void
     */
    public function setIsStandalone(bool $isStandalone): void;
}
