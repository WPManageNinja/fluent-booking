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
<h5>When:</h5>
<p><?php echo wp_kses_post($event_date); ?></p>
</div>

<div class="conf_section">
    <h5>Location:</h5>
    <p><?php echo wp_kses_post($event_location); ?></p>
</div>

<?php if($booking->message): ?>
<div class="conf_section">
    <h5>Guest's Note:</h5>
    <div><?php echo wpautop($booking->message); ?></div>
</div>
<?php endif; ?>

<div class="conf_section">
    <h5>Guests:</h5>
    <ul class="lists">
        <li><?php echo esc_attr($author_email); ?> - <span style="font-size: 80%; color: gray;">you</span></li>
        <li><?php echo esc_attr($booking->email); ?> - <span style="font-size: 80%; color: gray;">guest</span></li>
    </ul>
</div>
