<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ReadonlyField extends Field
{
    public function render(AbstractElement $element): string
    {
        $element->setReadonly(true);

        return parent::render($element);
    }
}
