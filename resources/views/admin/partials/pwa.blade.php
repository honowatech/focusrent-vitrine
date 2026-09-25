<meta name="theme-color" content="#0A0413">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="FR Admin">
<link rel="manifest" href="{{ url('/admin/manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('images/pwa/admin-192.png') }}">
<link rel="icon" href="{{ asset('images/pwa/admin-192.png') }}" type="image/png" sizes="192x192">
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register(@json(url('/admin/sw.js')), { scope: @json(url('/admin')) });
    });
}
window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    window.focusRentInstall = event;
    document.querySelectorAll('[data-pwa-install]').forEach(function (button) {
        button.hidden = false;
    });
});
document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-pwa-install]');
    if (!button || !window.focusRentInstall) {
        return;
    }
    window.focusRentInstall.prompt();
    window.focusRentInstall.userChoice.then(function () {
        window.focusRentInstall = null;
        button.hidden = true;
    });
});
</script>
