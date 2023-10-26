<div class="fcal_cal_wrap">
    <div class="fluent_booking_app" data-calendar_id="<?php echo (int)$calenderEvent->calendar_id; ?>"
         data-event_id="<?php echo (int)$calenderEvent->id; ?>"></div>
    <?php do_action('fluent_booking/short_code_render', $calenderEvent); ?>
</div>

