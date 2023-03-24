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

    if (lastLi.offset().top + lastLi.height() <= jQuery(window).height()) {
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
            if (playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED) {
                Chromecast.loadPlaylist();
            } else {
                Chromecast.loadList();
            }
        }

        firstLi.remove();
        Chromecast.animatePreview();
    });
}