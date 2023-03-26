Chromecast.pauseListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();
    const mediaWidth = Chromecast.media.width();
    const mediaHeight = Chromecast.media.height();

    playerManager.addEventListener(cast.framework.events.EventType.PAUSE, () => {
        const mediaInformation = playerManager.getMediaInformation();
        const duration = parseInt(mediaInformation.duration);
        const position = parseInt(playerManager.getCurrentTimeSec());

        if (duration === position) {
            return;
        }

        Chromecast.media.stop();
        Chromecast.getItem(
            mediaInformation.contentId,
            (item) => {
                item.duration = duration;
                item.position = position;
                Chromecast.setTopPreview(item);

                Chromecast.media.animate({
                    top: '100px',
                    left: '30px',
                    width: mediaWidth + 'px',
                    height: mediaHeight + 'px',
                }, 3000, () => {
                    Chromecast.loadPlaylist(() => {
                        Chromecast.footerUl.empty();
                        Chromecast.updatePreview();
                        Chromecast.animatePreview();
                    });
                });
                Chromecast.timeline.animate({
                    width: '75%',
                }, 3000);
            }
        );
    });
};