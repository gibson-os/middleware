Chromecast.customMessageListener = () => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

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
            if (playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED) {
                Chromecast.loadPlaylist(() => {
                    Chromecast.footerUl.empty();
                    Chromecast.updatePreview();
                    Chromecast.animatePreview();
                });
            } else {
                Chromecast.loadList(() => {
                    Chromecast.footerUl.empty();
                    Chromecast.updatePreview();
                    Chromecast.animatePreview();
                });
            }
        });
    });
};