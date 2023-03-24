Chromecast.loadStartListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.LOAD_START, () => {
        jQuery('footer li:first').stop();
        Chromecast.media.stop();
        Chromecast.media.css('backgroundSize', 'auto');
        Chromecast.media.css('backgroundImage', 'url(\'/img/loading.gif\')');
        Chromecast.media.css('display', 'block');
    });
};