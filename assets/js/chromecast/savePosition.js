Chromecast.savePositionRequestActive = false;
Chromecast.lastPosition = 0;
Chromecast.savePosition = () => {
    if (Chromecast.savePositionRequestActive) {
        return;
    }

    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();
    const position = parseInt(playerManager.getCurrentTimeSec());

    if (position === Chromecast.lastPosition) {
        return;
    }

    Chromecast.lastPosition = position;
    Chromecast.savePositionRequestActive = true;

    jQuery.ajax({
        url: '/middleware/chromecast/savePosition',
        method: 'POST',
        data: {
            id: castReceiverManager.getApplicationData().sessionId,
            token: playerManager.getMediaInformation().contentId,
            position: position,
            users: Chromecast.getConnectedUsers(),
        },
        complete() {
            window.setTimeout(() => {
                Chromecast.savePositionRequestActive = false;
            }, 3000);
        }
    });
}