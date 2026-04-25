<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Model\ApplePay\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Pem extends File
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Magento\Framework\Filesystem $filesystem,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return string[]
     */
    protected function _getAllowedExtensions(): array
    {
        return ['pem'];
    }

    protected function getUploadDirPath($uploadDir): string
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath($uploadDir);
    }
}
