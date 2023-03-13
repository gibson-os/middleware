Chromecast.showMessage = (message, image) => {
    let backgroundImage = 'none';

    if (image) {
        backgroundImage = 'url(\'' + image + '?width=' + Chromecast.messageImage.width() + '&height=' + Chromecast.messageImage.height() + '\')';
    }

    Chromecast.message.html(message);
    Chromecast.messageImage.css('background-image', backgroundImage);
    Chromecast.messageContainer.css('opacity', 1);
    window.setTimeout(() => {
        Chromecast.messageContainer.css('opacity', 0);
    }, 2500);
}