window.addEventListener('error', function (event) {
    Chromecast.sendError(event.error.stack);
});