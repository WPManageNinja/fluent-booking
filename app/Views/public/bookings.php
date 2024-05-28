<?php defined( 'ABSPATH' ) || exit; ?>

<div class="fcal_container">
    <div class="fcal_booking_header">
        <h2><?= esc_html($attributes['title']) ?></h2>
        <div class="fcal_booking_header_actions">
            <?php if ($attributes['filter'] == 'show') : ?>
                <form action="" method="GET">
                    <?php foreach ($period_options as $value => $label): ?>
                        <div class="fcal_radio_btn">
                            <input type="radio" name="booking_period" id="fcal_period_<?= esc_attr($value) ?>"
                                value="<?= esc_attr($value) ?>" <?= $booking_period == $value ? 'checked' : '' ?>
                                onchange="this.form.submit()">
                            <label for="fcal_period_<?= esc_attr($value) ?>"><?= esc_html($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="fcal_all_bookings">
        <div class="fcal_bookings">
            <div class="fcal_booking_wrapper">
                <?php foreach ($bookings as $booking) : ?>
                    <div class="fcal_booking" onclick="location.href='<?= esc_url($booking->getConfirmationUrl()); ?>'">
                        <div class="fcal_spot_wrapper <?= 'fcal_spot_status_' . esc_attr($booking->status) ?>">
                            <div class="fcal_spot_line">
                                <div class="fcal_spot_timing">
                                    <p class="fcal_booking_date"><?= esc_html($booking->booking_date); ?></p>
                                    <p class="fcal_booking_time"><?= esc_html($booking->booking_time); ?></p>
                                    <p class="fcal_booking_timezone">(<?= esc_html($booking->person_time_zone); ?>)</p>
                                </div>
                                <div class="fcal_spot_desc">
                                <h3 class="fcal_spot_title">
                                    <?= wp_kses_post($booking->getBookingTitle(true)); ?>
                                </h3>
                                    <div class="fcal_spot_desc_sub_info">
                                        <?php if ($booking->happening_status) : ?>
                                            <?php foreach ($booking->happening_status as $slug => $status) : ?>
                                                <div class="fcal_spot_happening">
                                                    <span class=<?= 'fcal_' . esc_attr($slug) ?>>
                                                        <?= esc_html($status) ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <span class="fcal_spot_period_status">
                                                <?= esc_html($booking->booking_status_text) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($booking->payment_status) : ?>
                                            <p class="fcal_spot_payment_status <?= esc_attr($booking->payment_status) ?>">
                                                <?= esc_html($booking->payment_status_text) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($booking->status == 'pending' && $booking->payment_status != 'pending') : ?>
                                            <p class="fcal_spot_period_status unconfirmed">
                                                <?= __('Unconfirmed') ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="fcal_spot_actions">
                                    <button class="fcal_plain_btn"
                                        onclick="location.href='<?= esc_url($booking->getConfirmationUrl()); ?>'">
                                        <?=__('View')?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($bookings->isEmpty()): ?>
                    <div class="fcal_no_bookings">
                        <p><?=esc_html($attributes['no_bookings'])?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($attributes['pagination'] == 'show' && $bookings->lastPage() > 1): ?>
        <ul class="fcal_pagination">
            <span><?=__('Total', 'fluent-booking-pro') . ' ' . esc_html($bookings->total())?></span>

            <form action="" method="GET">
                <select name="booking_per_page" id="fcal_booking_per_page" onchange="this.form.submit()">
                    <?php foreach ($page_options as $option): ?>
                        <option value="<?= $option; ?>" <?= $per_page == $option ? 'selected' : '' ?>>
                            <?= esc_html($option) . '/' . __('page', 'fluent-booking-pro') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($bookings->onFirstPage()): ?>
                <a class="fcal_btn prev disabled" aria-label="Previous page is disabled" role="button">«</a>
            <?php else: ?>
                <a class="fcal_btn prev" aria-label="Go to previous page" role="button"
                    href="<?= esc_url($bookings->previousPageUrl()); ?>">«
                </a>
            <?php endif; ?>

            <ul class="fcal_pager">
                <?php for ($page = $start_page; $page <= $end_page; $page++): ?>
                    <?php if ($page == $bookings->currentPage()): ?>
                        <li class="active"><span><?= esc_html($page); ?></span></li>
                    <?php else: ?>
                        <li><a aria-label="Go to page <?= esc_attr($page); ?>"
                            href="<?= esc_url($bookings->url($page)); ?>"><?= esc_html($page); ?></a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
            </ul>

            <?php if ($bookings->hasMorePages()): ?>
                <a class="fcal_btn next" aria-label="Go to next page" role="button"
                    href="<?= esc_url($bookings->nextPageUrl()); ?>">»
                </a>
            <?php else: ?>
                <a class="fcal_btn next disabled" aria-label="Next page is disabled" role="button">»</a>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</div>
