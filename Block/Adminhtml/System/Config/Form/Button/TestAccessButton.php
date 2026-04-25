<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\System\Config\Form\Button;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestAccessButton extends Field
{
    const TEST_ROUTE = 'przelewy24/test/access';

    /** @var string */
    protected $_template = 'PayPro_Przelewy24::system/config/form/button/test_access.phtml';

    public function getTestUrl(): string
    {
        return $this->getUrl(self::TEST_ROUTE);
    }

    public function render(AbstractElement $element): string
    {
        $this->setData('scope_type', $element->getScope());
        $this->setData('scope_id', $element->getScopeId());
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->setData('button_label', __($originalData['button_label']));

        return $this->_toHtml();
    }
}
