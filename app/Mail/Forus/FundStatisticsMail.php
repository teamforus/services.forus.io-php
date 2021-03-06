<?php

namespace App\Mail\Forus;

use App\Mail\ImplementationMail;
use App\Services\Forus\Notification\EmailFrom;
use Illuminate\Mail\Mailable;

/**
 * Class FundStatisticsMail
 * @package App\Mail\Vouchers
 */
class FundStatisticsMail extends ImplementationMail
{
    private $fundName;
    private $sponsorName;
    private $sponsorAmount;
    private $providerAmount;
    private $requestAmount;
    private $totalAmount;

    public function __construct(
        string $fundName,
        string $sponsorName,
        string $sponsorAmount,
        string $providerAmount,
        string $requestAmount,
        string $totalAmount,
        ?EmailFrom $emailFrom
    ) {
        $this->setMailFrom($emailFrom);

        $this->fundName = $fundName;
        $this->sponsorName = $sponsorName;
        $this->sponsorAmount = $sponsorAmount;
        $this->providerAmount = $providerAmount;
        $this->requestAmount = $requestAmount;
        $this->totalAmount = $totalAmount;
    }

    public function build(): Mailable
    {
        return $this->buildBase()
            ->subject(mail_trans('fund_statistics.title', [
                'sponsor_name' => $this->sponsorName,
                'fund_name' => $this->fundName
            ]))
            ->view('emails.forus.fund_statistics', [
                'fund_name' => $this->fundName,
                'sponsor_name' => $this->sponsorName,
                'sponsor_amount' => $this->sponsorAmount,
                'provider_amount' => $this->providerAmount,
                'request_amount' => $this->requestAmount,
                'total_amount' => $this->totalAmount
            ]);
    }
}
