Chromecast.customMessageListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();

    castReceiverManager.addCustomMessageListener('urn:x-cast:net.itronom.gibson', (event) => {
        Chromecast.connectedUsers[event.senderId] = event.data.user;
        Chromecast.connectedUserIds[event.senderId] = event.data.user.id;

        Chromecast.showMessage('Willkommen ' + event.data.user.user + '!');
        jQuery('footer li:first').stop();
        jQuery('footer li').remove();
        jQuery.ajax({
            url: '/middleware/chromecast/addUser',
            method: 'POST',
            data: {
                sessionId: castReceiverManager.getApplicationData().sessionId,
                userId: event.data.user.id,
                senderId: event.senderId
            },
        }).done(() => {
            Chromecast.footerUl.empty();
            Chromecast.loadList(() => {
                Chromecast.animatePreview();
            });
        });
    });
};