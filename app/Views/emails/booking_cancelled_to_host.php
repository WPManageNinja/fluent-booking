<p>Your booking has been cancelled.</p>

<div class="conf_section">
    <h5>Event Name:</h5>
    <p><?php echo wp_kses_post($event_name); ?></p>
</div>

<div class="conf_section">
    <h5>Guest Details:</h5>
    <ul>
        <li>
            <b>Name:</b> <?php echo esc_html($booking->first_name.' '.$booking->last_name); ?>
        </li>
        <li>
            <b>Email:</b> <?php echo esc_html($booking->email); ?>
        </li>
        <?php if($booking->phone): ?>
        <li>
            <b>Phone:</b> <?php echo esc_html($booking->phone); ?>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="conf_section">
    <h5>Date & Time:</h5>
    <p><?php echo wp_kses_post($event_date); ?></p>
</div>

<div class="conf_section">
    <h5>Location:</h5>
    <p><?php echo wp_kses_post($event_location); ?></p>
</div>

<div class="conf_section">
    <h5>Cancellation Reason</h5>
    <p><?php echo wp_kses_post($cancel_reason); ?></p>
</div>
