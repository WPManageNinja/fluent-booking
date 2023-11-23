<?php
    /**
     * @var $theme
     * @var $calenderEvent
     */

    $mode = '';
    if ($theme == 'dark') {
        $mode = 'fcal-dark-mode';
    } else if ($theme == 'light') {
        $mode = 'fcal-light-mode';
    }
?>
<div class="fcal_cal_wrap <?php echo esc_attr($mode); ?>">
    <div class="fluent_booking_app" data-calendar_id="<?php echo (int)$calenderEvent->calendar_id; ?>"
         data-event_id="<?php echo (int)$calenderEvent->id; ?>"></div>
    <?php do_action('fluent_booking/short_code_render', $calenderEvent); ?>
</div>

<script>
    const theme   = '<?php echo esc_attr($theme); ?>';
    const calwrap = document.querySelector('.fcal_cal_wrap');
    // System Mode
    if (theme == 'system-default') {
        const runColorMode = (fn) => {
            if (!window.matchMedia) {
                return;
            }
            const query = window.matchMedia('(prefers-color-scheme: dark)');
            fn(query.matches);
            query.addEventListener('change', (event) => fn(event.matches));
        }
        runColorMode((isDarkMode) => {
            if (isDarkMode) {
                if (calwrap) {
                    modeClassAddRemove(calwrap,'fcal-dark-mode', 'fcal-light-mode');
                }
            } else {
                if (calwrap) {
                    modeClassAddRemove(calwrap,'fcal-light-mode', 'fcal-dark-mode');
                }
            }
        });
        function modeClassAddRemove(elName, addClass, removeClass) {
            if (elName && addClass) {
                elName.classList.add(addClass);
            }
            if (elName && removeClass) {
                elName.classList.remove(removeClass);
            }
        }
    }
</script>

