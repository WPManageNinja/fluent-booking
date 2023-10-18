<?php
namespace FluentBooking\App\Services\PluginManager;


class Bootstrap
{
    public function register()
    {
        $apiUrl = 'https://fluentbooking.com/wp-admin/admin-ajax.php?action=fluent_booking_beta_testers&time='.time();
        new Updater($apiUrl, FLUENT_BOOKING_PRO_DIR_FILE, array(
            'version'   => FLUENT_BOOKING_VERSION,
            'license'   => 'fake_license_key',
            'item_name' => 'FluentBooking',
            'item_id'   => 'fluent-booking',
            'author'    => 'wpmanageninja'
        ),
            array(
                'license_status' => 'valid',
                'admin_page_url' => admin_url('admin.php?page=fluent-booking#/'),
                'purchase_url'   => 'https://fluentbooking.com',
                'plugin_title'   => 'Fluent Booking'
            )
        );

        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);
    }

    public function pluginRowMeta($links, $file)
    {
        if ('fluent-booking/fluent-booking.php' !== $file) {
            return $links;
        }

        $checkUpdateUrl = esc_url(admin_url('plugins.php?fluent-booking-pro-check-update=' . time()));

        $row_meta = array(
            'check_update' => '<a  style="color: #583fad;font-weight: 600;" href="' . $checkUpdateUrl . '" aria-label="' . esc_attr__('Check Update', 'fluentcampaign-pro') . '">' . esc_html__('Check Update', 'fluentcampaign-pro') . '</a>',
        );

        return array_merge($links, $row_meta);
    }
}
