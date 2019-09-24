<?php

namespace App\Mail\Products;

use App\Mail\ImplementationMail;

/**
 * Class FundCreatedMail
 * @package App\Mail\Funds\Forus
 */
class ProductRequestRejectedCriteriaMail extends ImplementationMail
{
    private $productName;

    public function __construct(
        string $email,
        string $productName
    ) {
        parent::__construct($email, null);

        $this->productName = $productName;
    }

    public function build(): ImplementationMail
    {
        return parent::build()
            ->subject(mail_trans('product_request_rejected_criteria.title', [
                'productName' => $this->productName
            ]))
            ->view('emails.products.product_request_rejected_criteria', [
                'productName' => $this->productName,
            ]);
    }
}
