<?php

namespace App\Http\Requests\Api\Platform\Vouchers;

use App\Http\Requests\BaseFormRequest;
use App\Models\Fund;
use App\Models\Voucher;
use App\Rules\ProductIdToVoucherRule;
use App\Rules\Vouchers\IdentityVoucherAddressRule;

/**
 * Class StoreProductVoucherRequest
 * @package App\Http\Requests\Api\Platform\Vouchers
 */
class StoreProductVoucherRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->isAuthenticated();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'voucher_address' => [
                'required',
                'exists:voucher_tokens,address',
                new IdentityVoucherAddressRule(
                    $this->auth_address(),
                    Voucher::TYPE_BUDGET,
                    Fund::TYPE_BUDGET
                )
            ],
            'product_id' => [
                'required',
                'exists:products,id',
                new ProductIdToVoucherRule($this->input('voucher_address'))
            ],
        ];
    }
}
