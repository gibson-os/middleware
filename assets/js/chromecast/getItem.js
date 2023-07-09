Chromecast.getItem = (contentId, callback) => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();

    jQuery.ajax({
        url: '/middleware/chromecast',
        method: 'GET',
        data: {
            id: castReceiverManager.getApplicationData().sessionId,
            token: contentId
        }
    }).done((data) => {
        if (callback) {
            callback(data.data);
        }
    });
}