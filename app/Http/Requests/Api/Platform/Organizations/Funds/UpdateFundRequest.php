<?php

namespace App\Http\Requests\Api\Platform\Organizations\Funds;

use App\Models\Fund;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateFundRequest
 * @property null|Fund $fund
 * @package App\Http\Requests\Api\Platform\Organizations\Funds
 */
class UpdateFundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $fund = $this->fund;
        $criteriaEditable = config('forus.features.dashboard.organizations.funds.criteria');
        $datesEditable = ($this->fund && $this->fund->state == Fund::STATE_WAITING);

        return array_merge([
            'name'                          => 'required|between:2,200',
            'product_categories'            => 'present|array',
            'product_categories.*'          => 'exists:product_categories,id',
            'notification_amount'           => 'nullable|numeric',
        ], $criteriaEditable ? [
            'criteria'                      => 'required|array',
            'criteria.*.id'                 => [
                'nullable',
                Rule::exists('fund_criteria', 'id')->where(function(
                    Builder $query
                ) use ($fund) {
                    $query->where('fund_id', $fund->id);
                })],
            'criteria.*.operator'           => 'required|in:=,<,>',
            'criteria.*.record_type_key'    => 'required|exists:record_types,key',
            'criteria.*.value'              => 'required|string|between:1,10',
        ]: [], $datesEditable ? [
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'after:' . $this->fund->created_at->addDays(5)->format('Y-m-d')
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after:start_date'
            ],
        ] : []);
    }
}
