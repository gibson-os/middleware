Chromecast.sendError = (message) => {
    jQuery.ajax({
        url: '/middleware/chromecast/error',
        method: 'POST',
        data: {
            sessionId: cast.framework.CastReceiverContext.getInstance().getApplicationData().sessionId,
            message: message
        }
    });
};