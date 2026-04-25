<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\System\Config\Form\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;
use PayPro\Przelewy24\ViewModel\PlacementViewModel;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Przelewy24Header extends Fieldset
{
    /**
     * @return string
     */
    protected function _getFieldsetCss(): string
    {
        return parent::_getFieldsetCss() . ' przelewy24-header';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element): string
    {
        return '';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element): string
    {
        // phpcs:ignore
        $description = __('Przelewy24 is a group of almost 200 e-commerce enthusiasts setting trends on the payment market and turning clients\' visions into innovative products. Thanks to this, we provide the widest range of payment services on the market, the highest level of service and comprehensive technological solutions.');
        $html = '<div class="przelewy24-payment-header">';
        $html .= '<div class="przelewy24-placement">';
        $html .= '<a href="' . PlacementViewModel::PRZELEWY24_PLACEMENT_LINK_URL . '">';
        $html .= '<img src="' . PlacementViewModel::PRZELEWY24_PLACEMENT_IMAGE_URL . '" alt="Przelewy24"/>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="przelewy24-logo"></div>';
        $html .= '<div class="przelewy24-description">' . $description . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isCollapseState($element): bool
    {
        return false;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element): string
    {
        return '';
    }
}
