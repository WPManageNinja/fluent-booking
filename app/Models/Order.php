<?php

namespace FluentBooking\App\Models;

use FluentBooking\Framework\Database\Orm\Model as BaseModel;

class Order extends BaseModel
{
    protected $table = 'fcal_orders';

    protected $guarded = ['id'];
    protected $fillable = [
        'status',
        'parent_id',
        'order_number',
        'type',
        'customer_id',
        'payment_method',
        'payment_method_type',
        'payment_mode',
        'payment_method_title',
        'currency',
        'subtotal',
        'discount_tax',
        'discount_total',
        'shipping_tax',
        'shipping_total',
        'tax_total',
        'total_amount',
        'total_paid',
        'rate',
        'note',
        'ip_address',
        'completed_at',
        'refunded_at',
        'uuid'
    ];

}