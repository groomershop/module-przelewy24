<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block;

class ConfigurableInfo extends \Magento\Payment\Block\ConfigurableInfo
{
    const PDF_TEMPLATE = 'PayPro_Przelewy24::info/pdf.phtml';

    public function toPdf(): string
    {
        $this->setTemplate(self::PDF_TEMPLATE);

        return $this->toHtml();
    }
}
