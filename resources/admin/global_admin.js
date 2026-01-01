jQuery(document).ready(function ($) {
    jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove();
});

function initColorMode() {
    const wpBodyContent = document.getElementById('wpbody-content');
    if (!wpBodyContent) return;

    const savedMode = localStorage.getItem('fcal_color_mode');
    if (savedMode === 'dark') {
        wpBodyContent.classList.add('dark');
        document.documentElement.classList.add('dark');
    } else {
        wpBodyContent.classList.remove('dark');
        document.documentElement.classList.remove('dark');
    }
}

window.toggleMobileMenu = function () {
    const mobileMenuLinks = document.getElementById('fcal_mobile_menu_list');
    mobileMenuLinks?.style.setProperty('display', mobileMenuLinks.style.display === 'flex' ? 'none' : 'flex');
};

window.toggleColorMode = function () {
    const wpBodyContent = document.getElementById('wpbody-content');

    if (!wpBodyContent) return;

    const isDark = wpBodyContent.classList.contains('dark');

    wpBodyContent.classList.toggle('dark', !isDark);
    document.documentElement.classList.toggle('dark', !isDark);

    localStorage.setItem('fcal_color_mode', isDark ? 'light' : 'dark');
};

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

initColorMode();
setCurrentDateTime();
setInterval(setCurrentDateTime, 60000);

