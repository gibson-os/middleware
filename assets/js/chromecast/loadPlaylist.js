Chromecast.loadPlaylist = (callback) => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const queueManager = castReceiverManager.getPlayerManager().getQueueManager();
    const currentItemIndex = queueManager.getCurrentItemIndex();
    const items = queueManager.getItems();

    const addToPreview = (item, i) => {
        Chromecast.addToPreview(item);
        i++;

        if (i < items.length) {
            Chromecast.getItem(items[i].media.contentId, (nextItem) => {
                addToPreview(nextItem, i);
            });
        } else if (callback) {
            callback();
        }
    }

    const nextItemIndex = currentItemIndex+1;

    if (nextItemIndex >= items.length) {
        Chromecast.loadList(callback);

        return;
    }

    Chromecast.getItem(items[nextItemIndex].media.contentId, (item) => {
        addToPreview(item, nextItemIndex);
    });
};