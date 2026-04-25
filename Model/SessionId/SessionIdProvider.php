<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

use PayPro\Przelewy24\Api\SessionId\SessionIdProviderInterface;

class SessionIdProvider implements SessionIdProviderInterface
{
    /**
     * @var \PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver
     */
    private $sessionIdVersionMetadataResolver;

    /**
     * @var \PayPro\Przelewy24\Model\SessionId\SessionIdPrzelewy24MethodIdResolver
     */
    private $sessionIdPrzelewy24MethodIdResolver;

    /**
     * @var \PayPro\Przelewy24\Model\SessionId\SessionIdMagentoPaymentMethodResolver
     */
    private $sessionIdMagentoPaymentMethodResolver;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityGenerator;

    public function __construct(
        \PayPro\Przelewy24\Model\SessionId\SessionIdVersionMetadataResolver $sessionIdVersionMetadataResolver,
        \PayPro\Przelewy24\Model\SessionId\SessionIdPrzelewy24MethodIdResolver $sessionIdPrzelewy24MethodIdResolver,
        \PayPro\Przelewy24\Model\SessionId\SessionIdMagentoPaymentMethodResolver $sessionIdMagentoPaymentMethodResolver,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityGenerator
    ) {
        $this->sessionIdVersionMetadataResolver = $sessionIdVersionMetadataResolver;
        $this->sessionIdPrzelewy24MethodIdResolver = $sessionIdPrzelewy24MethodIdResolver;
        $this->sessionIdMagentoPaymentMethodResolver = $sessionIdMagentoPaymentMethodResolver;
        $this->identityGenerator = $identityGenerator;
    }

    public function get(string $paymentMethod, ?array $paymentAdditional = []): string
    {
        return $this->sessionIdVersionMetadataResolver->resolve() .
            $this->sessionIdPrzelewy24MethodIdResolver->resolve($paymentMethod, $paymentAdditional) .
            $this->sessionIdMagentoPaymentMethodResolver->resolve($paymentMethod) .
            $this->identityGenerator->generateId();
    }
}
