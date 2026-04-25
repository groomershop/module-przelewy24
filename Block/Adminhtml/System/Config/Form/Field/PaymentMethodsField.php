<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use PayPro\Przelewy24\Controller\Adminhtml\Payment\Methods;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class PaymentMethodsField extends AbstractFieldArray
{
    private const UPDATE_URL_ROUTE = 'przelewy24/payment/methods';

    /** @var string  */
    protected $_template = 'PayPro_Przelewy24::system/config/form/field/payment_methods.phtml';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareToRender()
    {
        $this->addColumn('method', [
            'label'    => __('Method'),
        ]);
    }

    public function getMethods(): string
    {
        return (string) json_encode(array_values(array_map(function (DataObject $row) {
            return [
                'id' => (int) $row->getId(),
                'name' => $row->getName(),
                'img' => $row->getImg(),
                'fieldName' => $this->getElement()->getName(),
                'standalone' => (bool) $row->getStandalone(),
            ];
        }, $this->getArrayRows())));
    }

    public function getUpdateUrl(): string
    {
        $scopeType = $this->getElement()->getScope();
        $scopeId = $this->getElement()->getScopeId();

        return $this->getUrl(self::UPDATE_URL_ROUTE, [
            Methods::SCOPE_TYPE_PARAM => $scopeType,
            Methods::SCOPE_ID_PARAM => $scopeId,
        ]);
    }
}
