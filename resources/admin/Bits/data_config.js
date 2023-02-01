// Get All Timezones by Zone
export const copyToClipBoard = function (text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    el.focus();

    try {
        document.execCommand('copy');
    } catch (err) {
        console.log('Oops, unable to copy');
        window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
    }

    document.body.removeChild(el);
}
