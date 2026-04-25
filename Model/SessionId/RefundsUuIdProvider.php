<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

use PayPro\Przelewy24\Api\SessionId\RefundsUuIdProviderInterface;

class RefundsUuIdProvider implements RefundsUuIdProviderInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver
     */
    private $sessionIdVersionMetadataResolver;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityGenerator;

    public function __construct(
        \PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver $sessionIdVersionMetadataResolver,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityGenerator
    ) {
        $this->sessionIdVersionMetadataResolver = $sessionIdVersionMetadataResolver;
        $this->identityGenerator = $identityGenerator;
    }

    public function get(): string
    {
        return $this->sessionIdVersionMetadataResolver->resolve() .
            $this->identityGenerator->generateId();
    }
}
