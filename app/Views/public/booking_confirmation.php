<?php
/*
 * @var $booking \FluentBooking\App\Models\Booking
 */
?>
<div class="fcal_confirmation">
    <?php do_action('fluent_booking/booking_details_header', $booking); ?>
    <div class="fcal_confirm_header">
        <?php if ($booking->status == 'scheduled'): ?>
            <div class="fcal_check_holder" style="min-height: 50px;">
                <img style="max-width: 44px;"
                     src="<?php echo \FluentBooking\App\App::getInstance('url.assets'); ?>/images/check-mark.png; ?>">
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

        <?php if ($booking->canCancel()): ?>
            <?php if ($action_type == 'cancel'): ?>
                <div class="fcal_booking_manage fcal_cancellation_wrap fcal_action_<?php esc_attr_e($action_type); ?>">
                    <form id="fcal_cancellation_form" action="<?php echo $action_url; ?>" method="POST"
                          class="fcal_form_cancellation">
                        <label for="cancellation_reason">Reason for cancellation</label>
                        <div class="fcal_form_field">
                    <textarea placeholder="<?php esc_attr_e('Please provide cancellation reason', 'fluent-booking'); ?>"
                              name="cancellation_reason" id="cancellation_reason" rows="3"></textarea>
                        </div>
                        <div class="fcal_form_actions">
                            <a href="<?php echo $booking->getConfirmationUrl(); ?>"
                               class="fcal_btn fcal_btn_secondary"><?php esc_html_e('Nevermind', 'fluent-booking'); ?></a>
                            <button class="fcal_btn fcal_btn_primary fcal_cancel_btn"
                                    type="submit"><?php esc_html_e('Cancel Booking', 'fluent-booking'); ?></button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="fcal_booking_manage fcal_normal_booking_footer">
                    <?php echo __('Need to make a change?', 'fluent-booking') ?> <a href="<?php echo $booking->getRescheduleUrl(); ?>">Reschedule</a> or <a
                        href="<?php echo $booking->getCancelUrl(); ?>">Cancel</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($bookmarks): ?>
            <div class="fcal_booking_manage fcal_to_calendars">
                <span><?php _e('Add to calendar', 'fluent-booking'); ?></span>
                <div class="fcal_cal_items">
                    <?php foreach ($bookmarks as $bookmark): ?>
                    <div title="<?php esc_attr_e($bookmark['title']); ?>">
                        <a href="<?php echo $bookmark['url']; ?>" target="_blank" rel="noopener">
                            <img style="width: 20px; height: 20px;" src="<?php echo $bookmark['icon']; ?>" alt="<?php esc_attr_e($bookmark['title']); ?>"/>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php do_action('fluent_booking/booking_confirmation_footer', $booking); ?>
</div>
