Chromecast.debugOverlay = (value) => {
    Chromecast.debug.css('display', 'block');
    Chromecast.debug.html(JSON.stringify(value));
}