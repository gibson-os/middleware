Chromecast.requestQueueInsertListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.REQUEST_QUEUE_INSERT, (event) => {
        jQuery.each(event.requestData.items, (index, item) => {
            Chromecast.getItem(item.media.contentId, (mediaItem) => {
                Chromecast.showMessage(
                    mediaItem.filename + ' hinzugefügt',
                    '/middleware/chromecast/image/id/' + castReceiverManager.getApplicationData().sessionId + '/token/' + mediaItem.html5MediaToken + '/image.jpg'
                )
            });
        });
    });
};