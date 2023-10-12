<?php
namespace FluentBooking\App\Services\Integrations\PaymentMethods;

interface BasePaymentInterface
{
    public function isEnabled(): bool;

}
