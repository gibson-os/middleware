Chromecast.playingListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.PLAYING, () => {
        const mediaInformation = playerManager.getMediaInformation();
        Chromecast.title.html(mediaInformation.metadata.title);
        const isVideo = mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.VIDEO;
        Chromecast.footerUl.css('display', 'none');
        Chromecast.media.css('zIndex', isVideo ? 99999 : 99);
        Chromecast.media.css('backgroundSize', 'cover');
        Chromecast.media.css(
            'backgroundImage',
            isVideo
                ? 'none'
                : 'url(\'/middleware/chromecast/image' +
                '/id/' + castReceiverManager.getApplicationData().sessionId +
                '/token/' + mediaInformation.contentId +
                '/image.jpg?width=' + jQuery(window).width() + '\')'
        );
        Chromecast.media.animate({
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
        }, 3000);

        if (!isVideo) {
            Chromecast.timeline.animate({
                width: '100%',
            }, 3000);
        }
    });
};