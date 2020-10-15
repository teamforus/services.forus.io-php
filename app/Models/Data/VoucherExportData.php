<?php

namespace App\Models\Data;

use App\Models\Voucher;

/**
 * Class VoucherExportData
 * @property Voucher $voucher
 * @package App\Models\Data
 */
class VoucherExportData
{
    protected $voucher;
    protected $name;

    public function __construct(Voucher $voucher)
    {
        $this->name = token_generator()->generate(6, 2);
        $this->voucher = $voucher;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Voucher
     */
    public function getVoucher(): Voucher
    {
        return $this->voucher;
    }

    public function toArray(): array {
        $assigned_to_identity = $this->voucher->identity_address && $this->voucher->is_granted;

        return array_merge([
            'name' => $this->name,
            'granted' => $assigned_to_identity ? 'Ja': 'Nee',
            'in_use' => $this->voucher->has_transactions ? 'Ja': 'Nee',
        ], $this->voucher->product ? [
            'product_name' => $this->voucher->product->name,
        ] : [], $assigned_to_identity ? [
            'identity_bsn' => record_repo()->bsnByAddress($this->voucher->identity_address),
            'identity_email' => record_repo()->primaryEmailByAddress($this->voucher->identity_address),
        ] : [
            'identity_bsn' => null,
            'identity_email' => null,
        ], [
            'note' => $this->voucher->note,
            'source' => $this->voucher->employee_id ? 'employee': 'user',
            'amount' => $this->voucher->amount,
            'fund_name' => $this->voucher->fund->name,
            'created_at' => format_date_locale($this->voucher->created_at),
            'expire_at' => format_date_locale($this->voucher->expire_at),
        ]);
    }
}