Chromecast.animatePreview = () => {
    const playerManager = cast.framework.CastReceiverContext.getInstance().getPlayerManager();

    if (
        playerManager.getPlayerState() !== cast.framework.messages.PlayerState.IDLE &&
        playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
    ) {
        return;
    }

    Chromecast.footerUl.css('display', 'block');
    let firstLi = jQuery('footer li:first');
    let lastLi = jQuery('footer li:last');

    const loadList = (callback) => {
        if (playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED) {
            Chromecast.loadPlaylist(callback);
        } else {
            Chromecast.loadList(callback);
        }
    };

    if (lastLi.offset().top + lastLi.height() <= jQuery(window).height()) {
        window.setTimeout(() => {
            loadList(() => {
                Chromecast.footerUl.empty();
                Chromecast.updatePreview();
                Chromecast.animatePreview();
            });
        }, 10000);

        return;
    }

    firstLi.animate({
        marginTop: '-' + firstLi.height() + 'px',
    }, 5000, 'linear', () => {
        if (
            playerManager.getPlayerState() !== cast.framework.messages.PlayerState.IDLE &&
            playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
        ) {
            Chromecast.footerUl.css('display', 'none');
            return;
        }

        let lastLi = jQuery('footer li:last');

        if (lastLi.offset().top <= jQuery(window).height()) {
            loadList(() =>{
                Chromecast.updatePreview();
            });
        }

        firstLi.remove();
        Chromecast.animatePreview();
    });
}