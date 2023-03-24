Chromecast.getItem = (contentId, callback) => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();

    jQuery.ajax({
        url: '/middleware/chromecast/get',
        method: 'POST',
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