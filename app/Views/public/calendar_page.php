<div id="<?php echo esc_attr($wrapper_id); ?>" class="fcal_calendar_wrapper <?php echo esc_attr($wrapper_class); ?>">
    <div class="fcal_calendar_block_inner">
        <div style="height: 250px;width: 100%;text-align: center;display: flex;align-items: center;justify-content: center;flex-basis: max-content;flex-direction: column;" class="fcal_calendar_loading">
            <h3><?php esc_html_e('Loading....', 'fluent-booking-pro'); ?></h3>
            <i class="fcal-inline-spinner"></i>
            <style>
                @keyframes fcal-inline-spinner-kf {
                    0% {
                        transform: rotate(0deg);
                    }
                    100% {
                        transform: rotate(360deg);
                    }
                }

                .fcal-inline-spinner,
                .fcal-inline-spinner:before {
                    display: inline-block;
                    width: 111px;
                    height: 111px;
                    transform-origin: 50%;
                    border: 2px solid transparent;
                    border-color: #74a8d0 #74a8d0 transparent transparent;
                    border-radius: 50%;
                    content: "";
                    animation: linear fcal-inline-spinner-kf 900ms infinite;
                    position: relative;
                    vertical-align: inherit;
                    line-height: inherit;
                }
                .c-inline-spinner {
                    top: 3px;
                    margin: 0 3px;
                }
                .c-inline-spinner:before {
                    border-color: #74a8d0 #74a8d0 transparent transparent;
                    position: absolute;
                    left: -2px;
                    top: -2px;
                    border-style: solid;
                }
            </style>
        </div>
    </div>
</div>

<style>
    .fcal_phone_wrapper .flag {
        background: url(<?php echo esc_url(\FluentBooking\App\App::getInstance()['url.assets'].'images/flags_responsive.png'); ?>) no-repeat;
        background-size: 100%;
    }
</style>
