<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class RefundTransactionResponseValidator extends AbstractValidator
{
    private const STATUS = 'status';
    private const MESSAGE = 'message';

    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \PayPro\Przelewy24\Model\ErrorFormatter
     */
    private $errorFormatter;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader,
        \PayPro\Przelewy24\Model\ErrorFormatter $errorFormatter
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->errorFormatter = $errorFormatter;
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        $isValid = true;
        $errors = [];
        foreach ($response as $refundItem) {
            if ($refundItem[self::STATUS] !== true) {
                $isValid = false;
                $errors[] = $this->errorFormatter->format($refundItem[self::MESSAGE], $validationSubject);
            }
        }

        return $this->createResult($isValid, $errors);
    }
}
