Chromecast.init = () => {
    Chromecast.media = jQuery('#media');
    Chromecast.title = jQuery('#title');
    Chromecast.image = jQuery('#image');
    Chromecast.nextFiles = jQuery('#nextFiles');
    Chromecast.footer = jQuery('footer');
    Chromecast.footerUl = jQuery('footer ul');
    Chromecast.messageContainer = jQuery('#messageContainer');
    Chromecast.message = jQuery('#message');
    Chromecast.messageImage = jQuery('#messageImage');
    Chromecast.time = jQuery('#time');
    Chromecast.timeline = jQuery('#timeline');
    Chromecast.timelineBar = jQuery('#timeline .bar');
    Chromecast.timelineDuration = jQuery('#timeline .duration');
    Chromecast.timelinePosition = jQuery('#timeline .bar div.position');
    Chromecast.timelineCurrentPosition = jQuery('#timeline .currentPosition');
    Chromecast.previewItems = [];

    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    Chromecast.playingListener();
    Chromecast.customMessageListener();
    Chromecast.loadStartListener();
    Chromecast.pauseListener();
    Chromecast.timeUpdateListener();
    Chromecast.endedListener();
    Chromecast.errorListener();
    Chromecast.requestQueueInsertListener();

    playerManager.setMediaElement(Chromecast.media.get(0));
    castReceiverManager.start();

    window.setInterval(() => {
        let date = new Date();
        let hour = date.getHours();
        let minute = date.getMinutes();

        if (hour < 10) {
            hour = '0' + hour;
        }

        if (minute < 10) {
            minute = '0' + minute;
        }

        Chromecast.time.html(hour + ':' + minute);
    }, 800);
}