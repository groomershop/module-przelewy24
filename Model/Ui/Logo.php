<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\Ui;

use Magento\Framework\Exception\LocalizedException;

class Logo
{
    const PRZELEWY24_FILE_ID = 'PayPro_Przelewy24::images/logo.svg';
    const CARD_FILE_ID = 'PayPro_Przelewy24::images/logo-card.svg';
    const BLIK_FILE_ID = 'PayPro_Przelewy24::images/logo-blik.svg';
    const GOOGLE_PAY_FILE_ID = 'PayPro_Przelewy24::images/logo-google-pay.svg';
    const APPLE_PAY_FILE_ID = 'PayPro_Przelewy24::images/logo-apple-pay.svg';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Asset\Repository $assetRepository
    ) {
        $this->request = $request;
        $this->assetRepository = $assetRepository;
    }

    public function getUrl(): ?string
    {
        return $this->getImageUrl(self::PRZELEWY24_FILE_ID);
    }

    public function getCardUrl(): ?string
    {
        return $this->getImageUrl(self::CARD_FILE_ID);
    }

    public function getBlikUrl(): ?string
    {
        return $this->getImageUrl(self::BLIK_FILE_ID);
    }

    public function getGooglePayUrl(): ?string
    {
        return $this->getImageUrl(self::GOOGLE_PAY_FILE_ID);
    }

    public function getApplePayUrl(): ?string
    {
        return $this->getImageUrl(self::APPLE_PAY_FILE_ID);
    }

    private function getImageUrl(string $fileId): ?string
    {
        try {
            $asset = $this->assetRepository->createAsset($fileId, ['_secure' => $this->request->isSecure()]);
        } catch (LocalizedException $e) {
            return null;
        }

        return $asset->getUrl();
    }
}
