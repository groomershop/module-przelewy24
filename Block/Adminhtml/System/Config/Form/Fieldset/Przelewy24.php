<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\System\Config\Form\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;

class Przelewy24 extends Fieldset
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return string
     */
    protected function _getFieldsetCss(): string
    {
        return parent::_getFieldsetCss() . ' przelewy24-fieldset';
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element): string
    {
        return '<span class="przelewy24-fieldset-title" id="' . $element->getHtmlId() . '-head">'
            . $element->getLegend()
            . '</span>';
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isCollapseState($element): bool
    {
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element): string
    {
        return '';
    }
}
