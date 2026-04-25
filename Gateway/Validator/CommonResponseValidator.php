<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CommonResponseValidator extends AbstractValidator
{
    const ERROR = 'error';

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
        $error = $this->subjectReader->readResponseError($validationSubject);

        $errors = [];
        if ($error !== null) {
            $errors[] = $this->errorFormatter->format($error, $validationSubject);
        }

        return $this->createResult($error === null, $errors);
    }
}
