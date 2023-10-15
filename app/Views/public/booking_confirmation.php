<?php
/*
 * @var $booking \FluentBooking\App\Models\Booking
 */
?>
<div class="fcal_confirmation">
    <?php do_action('fluent_booking/booking_details_header', $booking); ?>
    <div class="fcal_confirm_header">
        <?php if($booking->status == 'scheduled'): ?>
        <div class="fcal_check_holder" style="min-height: 50px;">
            <img style="max-width: 44px;" src="<?php echo \FluentBooking\App\App::getInstance('url.assets'); ?>/images/check-mark.png; ?>">
        </div>
        <?php endif; ?>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo wp_kses_post($sub_heading); ?></p>
    </div>
    <div class="fcal_confirm_body">
        <?php foreach ($sections as $section): ?>
            <div class="fcal_confirm_section">
                <div class="fcal_confirm_section_title">
                    <h4><?php echo esc_html($section['title']); ?></h4>
                </div>
                <div class="fcal_confirm_section_content">
                    <?php echo wp_kses_post($section['content']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="fcal_booking_manage">
        Need to make a change? <a href="<?php echo $booking->getRescheduleUrl(); ?>">Reschedule</a> or <a href="<?php echo $booking->getCancelUrl(); ?>">Cancel</a>
    </div>
    <?php do_action('fluent_booking/booking_confirmation_footer', $booking); ?>
</div>
