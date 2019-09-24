<?php

namespace App\Mail\Products;

use App\Mail\ImplementationMail;

/**
 * Class FundCreatedMail
 * @package App\Mail\Funds\Forus
 */
class ProductRequestRejectedMail extends ImplementationMail
{
    private $productName;
    private $recordTypeName;
    private $recordTypeValue;

    public function __construct(
        string $email,
        string $productName,
        string $recordTypeName,
        string $recordTypeValue
    ) {
        parent::__construct($email, null);

        $this->productName = $productName;
        $this->recordTypeName = $recordTypeName;
        $this->recordTypeValue = $recordTypeValue;
    }

    public function build(): ImplementationMail
    {
        return parent::build()
            ->subject(mail_trans('product_request_rejected.title', [
                'productName' => $this->productName
            ]))
            ->view('emails.products.product_request_rejected', [
                'productName' => $this->productName,
                'recordTypeName' => $this->recordTypeName,
                'recordTypeValue' => $this->recordTypeValue,
            ]);
    }
}
