<p>Your booking has been cancelled.</p>

<div class="conf_section">
    <h5>Event Name:</h5>
    <p><?php echo wp_kses_post($event_name); ?></p>
</div>

<div class="conf_section">
    <h5>Date & Time:</h5>
    <p><?php echo wp_kses_post($event_date); ?></p>
</div>

<div class="conf_section">
    <h5>Cancellation Reason</h5>
    <p><?php echo wp_kses_post($cancel_reason); ?></p>
</div>
