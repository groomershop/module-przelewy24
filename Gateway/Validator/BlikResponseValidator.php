<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class BlikResponseValidator extends AbstractValidator
{
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
        $errors = [];
        $isValid = true;
        if (empty($response['orderId'])) {
            $isValid = false;
            $errors[] = $this->errorFormatter->format('transaction id is missing', $validationSubject);
        }

        return $this->createResult($isValid, $errors);
    }
}
