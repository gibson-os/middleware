Chromecast.showMessage = (message, image) => {
    let backgroundImage = 'none';

    if (image) {
        backgroundImage = 'url(\'' + image + '?width=' + Chromecast.messageImage.width() + '&height=' + Chromecast.messageImage.height() + '\')';
    }

    Chromecast.message.html(message);
    Chromecast.messageImage.css('background-image', backgroundImage);
    Chromecast.messageContainer.css('display', 'block');
    window.setTimeout(() => {
        Chromecast.messageContainer.css('display', 'none');
    }, 2500);
}