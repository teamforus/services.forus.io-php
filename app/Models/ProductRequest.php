<?php

namespace App\Models;

use App\Events\Vouchers\VoucherCreated;
use App\Mail\Products\ProductRequestRejectedCriteriaMail;
use App\Mail\Products\ProductRequestRejectedMail;
use App\Policies\FundPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ValidatorRequest
 * @property mixed $id
 * @property int $product_id
 * @property int $fund_id
 * @property string $identity_address
 * @property Product $product
 * @property Fund $fund
 * @property Carbon $resolved_at
 * @property Collection|ValidatorRequest[] $validator_requests
 * @package App\Models
 */
class ProductRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'fund_id',
    ];

    protected $dates = [
        'resolved_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function validator_requests() {
        return $this->hasMany(ValidatorRequest::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function validator_requests_rejected() {
        return $this->validator_requests()->where('state', '!=','approved');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function validator_requests_approved() {
        return $this->validator_requests()->where('state', 'approved');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fund() {
        return $this->belongsTo(Fund::class);
    }

    /**
     * Check whatever all validation requests attached to this product
     * request are approved.
     * @return bool
     */
    public function isFulfilled() {
        return $this->validator_requests_approved()->count() ==
            $this->validator_requests()->count();
    }

    /**
     * Check whatever all validation requests attached to this product
     * request are approved.
     * @return bool
     */
    public function isRejected() {
        return $this->validator_requests_rejected()->count() > 0;
    }

    /**
     * Check whatever requester records meet requested fund criteria.
     * @return bool
     */
    public function isFundCriteriaMeet() {
        try {
            return (new FundPolicy)->apply(
                $this->identity_address,
                $this->fund,
                $this->product
            );
        } catch (\Exception $exception) {
            logger()->debug($exception);
            return false;
        }
    }

    /**
     * @return mixed|null
     */
    public function identityEmail() {
        return resolve('forus.services.record')
            ->primaryEmailByAddress($this->validator_requests[0]->identity_address);
    }

    /**
     * Create product voucher or send rejection email
     * @param ValidatorRequest $validatorRequest
     */
    public function handleUpdate(
        ValidatorRequest $validatorRequest
    ) {
        $sender = resolve('mailer');
        $recordRepo = resolve('forus.services.record');
        $request = $recordRepo->showValidationRequest(
            $validatorRequest->record_validation_uid
        );

        if ($this->isRejected()) {
            logger()->debug('rejected');
            // Send rejection email
            $sender->send(new ProductRequestRejectedMail(
                $this->identityEmail(),
                $this->product->name,
                $request['name'],
                $request['value']
            ));
        }

        if (!$this->isFulfilled()) {
            logger()->debug('not fulfilled');
            return;
        }

        if ($this->isFundCriteriaMeet()) {
            logger()->debug('criteria meet');
            /** @var Voucher $regularVoucher */
            $regularVoucher = $this->fund->makeVoucher($validatorRequest->identity_address);
            $voucherExpireAt = $regularVoucher->fund->end_date->gt(
                $regularVoucher->expire_at
            ) ? $this->product->expire_at : $regularVoucher->fund->end_date;

            $voucher = Voucher::create([
                'identity_address' => $regularVoucher->identity_address,
                'parent_id' => $regularVoucher->id,
                'fund_id' => $regularVoucher->fund_id,
                'product_id' => $this->product->id,
                'amount' => $this->product->price,
                'expire_at' => $voucherExpireAt
            ]);

            VoucherCreated::dispatch($voucher);
        } else {
            logger()->debug('criteria not meet');
            // Send validated but criteria unmet email
            $sender->send(new ProductRequestRejectedCriteriaMail(
                $this->identityEmail(),
                $this->product->name
            ));
        }
    }
}
