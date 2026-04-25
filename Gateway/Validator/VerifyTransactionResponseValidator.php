<?php
declare(strict_types=1);

namespace PayPro\Przelewy24\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class VerifyTransactionResponseValidator extends AbstractValidator
{
    private const STATUS = 'status';
    private const SUCCESS_STATUS = 'success';
    /**
     * @var \PayPro\Przelewy24\Gateway\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \PayPro\Przelewy24\Gateway\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        $result = $response[self::STATUS] ?? null;

        return $this->createResult($result === self::SUCCESS_STATUS);
    }
}
