<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\SessionId;

use Magento\Framework\Component\ComponentRegistrar;
use PayPro\Przelewy24\Api\SessionId\PackageVersionProviderInterface;

class PackageVersionProvider implements PackageVersionProviderInterface
{
    const COMPONENT_REGISTRAR_NAME = 'PayPro_Przelewy24';
    const COMPOSER_JSON_FILE = 'composer.json';

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    public function __construct(
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
    }

    public function get(): string
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::COMPONENT_REGISTRAR_NAME);
        $directoryRead = $this->readFactory->create((string)$path);
        $composerJsonData = $directoryRead->readFile(self::COMPOSER_JSON_FILE);
        $data = json_decode($composerJsonData);
        return $data->version ?? '';
    }
}
