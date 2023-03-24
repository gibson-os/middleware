Chromecast.errorListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.ERROR, (event) => {
        Chromecast.sendError(JSON.stringify(event));
    });
};