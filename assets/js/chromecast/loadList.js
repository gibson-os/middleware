Chromecast.loadList = (callback) => {
    const playerManager = cast.framework.CastReceiverContext.getInstance().getPlayerManager();

    jQuery.ajax({
        url: '/middleware/chromecast/toSeeList',
        method: 'POST',
        data: {
            id: cast.framework.CastReceiverContext.getInstance().getApplicationData().sessionId
        },
    }).done((data) => {
        jQuery.each(data.data, (position, item) => {
            if (
                position === 0 &&
                playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
            ) {
                Chromecast.setTopPreview(item);
            } else {
                Chromecast.addToPreview(item);
            }
        });

        if (callback) {
            callback(data);
        }
    });
};
