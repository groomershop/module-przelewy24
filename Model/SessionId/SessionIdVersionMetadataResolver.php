<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

class SessionIdVersionMetadataResolver
{
    const SESSION_ID_PART = 'magp24{%s:%s}_';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \PayPro\Przelewy24\Api\SessionId\PackageVersionProviderInterface
     */
    private $packageVersionProvider;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \PayPro\Przelewy24\Api\SessionId\PackageVersionProviderInterface $packageVersionProvider
    ) {
        $this->productMetadata = $productMetadata;
        $this->packageVersionProvider = $packageVersionProvider;
    }

    public function resolve(): string
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $packageVersion = $this->packageVersionProvider->get();

        return sprintf(self::SESSION_ID_PART, $magentoVersion, $packageVersion);
    }
}
