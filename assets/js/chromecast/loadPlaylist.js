Chromecast.loadPlaylist = (callback) => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const queueManager = castReceiverManager.getQueueManager();
    const currentItemIndex = queueManager.getCurrentItemIndex();
    const items = queueManager.getItems();

    const addToPreview = (item, i) => {
        Chromecast.addToPreview(item);

        if (i < items.length) {
            i++;

            Chromecast.getItem(items[i].media.contentId, (nextItem) => {
                addToPreview(nextItem.media.contentId, i);
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