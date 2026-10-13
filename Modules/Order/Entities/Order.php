<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\People\Entities\Customer;
use Modules\Sale\Entities\Sale;

/**
 * Pesanan online dari pelanggan. Harga disimpan dalam Rupiah (tanpa desimal).
 */
class Order extends Model
{
    const STATUS_WAITING = 'Menunggu Konfirmasi';
    const STATUS_PROCESSING = 'Diproses';
    const STATUS_SHIPPING = 'Dikirim';
    const STATUS_DONE = 'Selesai';
    const STATUS_CANCELLED = 'Dibatalkan';

    const STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPING,
        self::STATUS_DONE,
        self::STATUS_CANCELLED,
    ];

    const DELIVERY_METHODS = ['Ambil di Toko', 'Diantar Kurir Toko'];

    const PAYMENT_METHODS = ['Bayar di Tempat (COD)', 'Transfer Bank'];

    protected $guarded = [];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->order_code ??= 'PSN-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        });
    }

    public function getStatusBadgeAttribute() {
        return [
            self::STATUS_WAITING    => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_SHIPPING   => 'primary',
            self::STATUS_DONE       => 'success',
            self::STATUS_CANCELLED  => 'danger',
        ][$this->status] ?? 'secondary';
    }
}
