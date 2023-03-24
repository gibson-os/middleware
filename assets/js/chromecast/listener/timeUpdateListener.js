Chromecast.timeUpdateListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.TIME_UPDATE, () => {
        const mediaInformation = playerManager.getMediaInformation();
        Chromecast.savePosition();

        if (
            playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED ||
            mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.AUDIO
        ) {
            const duration = parseInt(mediaInformation.duration);
            const position = parseInt(playerManager.getCurrentTimeSec());

            Chromecast.timelineDuration.html(duration.toTimeFormat());
            Chromecast.timelineCurrentPosition.html(position.toTimeFormat());
            Chromecast.timelineCurrentPosition.css('width', ((100 / duration) * position) + '%');
            Chromecast.timelinePosition.css('width', ((100 / duration) * position) + '%');
        }
    });
};