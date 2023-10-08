<?php

namespace FluentBooking\App\Models;


/**
 *  Webhook Model - DB Model for Webhooks
 *  Database Model
 * @package FluentBooking\App\Models
 * @version 1.0.0
 */

class Webhook extends Meta
{
    protected $fillable = [
        'key',
        'value',
        'object_type',
        'object_id'
    ];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('type', function ($builder) {
            $builder->where('object_type', '=', 'webhook');
        });
    }

    public static function store($slot_id, $data)
    {
        return static::create([
            'object_id'   => $slot_id,
            'object_type' => 'webhook',
            'key'         => 'webhook_settings',
            'value'       => $data,
        ]);
    }
}