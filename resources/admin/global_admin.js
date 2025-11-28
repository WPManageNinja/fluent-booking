jQuery(document).ready(function ($) {
    let $handHeld = $('.fframe_handheld');
    $handHeld.on('click', function () {
        $(this).parent().find('.fframe_menu').toggleClass('fframe_menu_open');
    });
    $('.fframe_menu_item a').on('click', function () {
        $handHeld.parent().find('.fframe_menu').removeClass('fframe_menu_open');
    });

    jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove();
});

function setCurrentDateTime() {
    const serverTimeStamp = parseInt(document.getElementById('fcal_server_timestamp').getAttribute('data-timestamp'), 10);
    const date            = new Date(serverTimeStamp * 1000); // Convert to milliseconds

    const formattedDate =
        date.getUTCFullYear() + '-' +
        ('0' + (date.getUTCMonth() + 1)).slice(-2) + '-' +
        ('0' + date.getUTCDate()).slice(-2) + ' ' +
        ('0' + (date.getUTCHours() % 12 || 12)).slice(-2) + ':' +
        ('0' + date.getUTCMinutes()).slice(-2) +
        (date.getUTCHours() < 12 ? ' am' : ' pm');

    document.getElementById('fcal_server_timestamp').textContent = 'Server Time: ' + formattedDate;
    document.getElementById('fcal_server_timestamp').setAttribute('data-timestamp', serverTimeStamp + 60);
}

setCurrentDateTime();
setInterval(setCurrentDateTime, 60000);

