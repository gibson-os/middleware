Chromecast.endedListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();
    const mediaWidth = Chromecast.media.width();
    const mediaHeight = Chromecast.media.height();

    playerManager.addEventListener(cast.framework.events.EventType.ENDED, () => {
        Chromecast.media.css('display', 'none');
        Chromecast.media.css('top', '100px');
        Chromecast.media.css('left', '30px');
        Chromecast.media.css('width', mediaWidth + 'px');
        Chromecast.media.css('height', mediaHeight + 'px');
        Chromecast.timeline.css('width', '75%');

        Chromecast.loadList(() => {
            Chromecast.animatePreview();
        });
    });
};