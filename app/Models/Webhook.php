<?php

namespace FluentBooking\App\Models;


class Webhook extends Meta
{
    protected $fillable = [
        'id',
        'key',
        'value',
        'object_type'
    ];

    public static function boot()
    {
        static::addGlobalScope('type', function ($builder) {
            $builder->where('object_type', '=', 'webhook');
        });
    }

    public function store($data)
    {
        return static::create([
            'object_type' => 'webhook',
            'key'         => $key = wp_generate_uuid4(),
            'value'       => array_merge($data, [
                'url' => site_url("?fluentbooking=1&route=calendar&hash={$key}")
            ]),
        ]);
    }
}