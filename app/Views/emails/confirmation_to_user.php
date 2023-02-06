<div class="conf_section">
    <h5>Event Name:</h5>
    <p><?php echo wp_kses_post($event_name); ?></p>
</div>

<div class="conf_section">
<h5>When:</h5>
<p><?php echo wp_kses_post($event_date); ?></p>
</div>

<div class="conf_section">
    <h5>Location:</h5>
    <p><?php echo wp_kses_post($event_location); ?></p>
</div>

<?php if($booking->message): ?>
<div class="conf_section">
    <h5>Your Note:</h5>
    <div><?php echo wpautop($booking->message); ?></div>
</div>
<?php endif; ?>

<div class="conf_section">
    <h5>Guests:</h5>
    <ul class="lists">
        <li><?php echo esc_attr($author_email); ?> - <span style="font-size: 80%; color: gray;">organizer</span></li>
        <li><?php echo esc_attr($booking->email); ?> - <span style="font-size: 80%; color: gray;">you</span></li>
    </ul>
</div>

