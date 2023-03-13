Chromecast.init = () => {
    Chromecast.media = jQuery('#media');
    Chromecast.title = jQuery('#title');
    Chromecast.image = jQuery('#image');
    Chromecast.nextFiles = jQuery('#nextFiles');
    Chromecast.footer = jQuery('footer');
    Chromecast.footerUl = jQuery('footer ul');
    Chromecast.messageContainer = jQuery('#messageContainer');
    Chromecast.message = jQuery('#message');
    Chromecast.messageImage = jQuery('#messageImage');
    Chromecast.debug = jQuery('#debug');
    Chromecast.time = jQuery('#time');
    Chromecast.timeline = jQuery('#timeline');
    Chromecast.timelineBar = jQuery('#timeline .bar');
    Chromecast.timelineDuration = jQuery('#timeline .duration');
    Chromecast.timelinePosition = jQuery('#timeline .bar div.position');
    Chromecast.timelineCurrentPosition = jQuery('#timeline .currentPosition');

    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();

    playerManager.addEventListener(cast.framework.events.EventType.PLAYING, () => {
        Chromecast.title.html(playerManager.getMediaInformation().metadata.title);
    });

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
            Chromecast.loadList(() => {
                Chromecast.animatePreview();
            });
        });
    });

    playerManager.setMediaElement(Chromecast.media.get(0));
    castReceiverManager.start();

    let mediaWidth = Chromecast.media.width();
    let mediaHeight = Chromecast.media.height();

    playerManager.addEventListener(cast.framework.events.EventType.LOAD_START, () => {
        jQuery('footer li:first').stop();
        Chromecast.media.stop();
        Chromecast.media.css('backgroundSize', 'auto');
        Chromecast.media.css('backgroundImage', 'url(\'/img/loading.gif\')');
        Chromecast.media.css('display', 'block');
    });
    playerManager.addEventListener(cast.framework.events.EventType.ERROR, (event) => {
        Chromecast.debugOverlay(event);
    });
    playerManager.addEventListener(cast.framework.events.EventType.PLAYING, () => {
        const mediaInformation = playerManager.getMediaInformation();
        Chromecast.title.html(mediaInformation.metadata.title);
        const isVideo = mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.VIDEO;
        Chromecast.footerUl.css('display', 'none');
        Chromecast.media.css('zIndex', isVideo ? 99999 : 99);
        Chromecast.media.css('backgroundSize', 'cover');
        Chromecast.media.css(
            'backgroundImage',
            isVideo
                ? 'none'
                : 'url(\'/middleware/chromecast/image' +
                '/id/' + castReceiverManager.getApplicationData().sessionId +
                '/token/' + mediaInformation.contentId +
                '/image.jpg?width=' + jQuery(window).width() + '\')'
        );
        Chromecast.media.animate({
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
        }, 3000);

        if (!isVideo) {
            Chromecast.timeline.animate({
                width: '100%',
            }, 3000);
        }
    });
    playerManager.addEventListener(cast.framework.events.EventType.PAUSE, () => {
        const mediaInformation = playerManager.getMediaInformation();
        const duration = parseInt(mediaInformation.duration);
        const position = parseInt(playerManager.getCurrentTimeSec());

        if (duration === position) {
            return;
        }

        Chromecast.media.stop();

        jQuery.ajax({
            url: '/middleware/chromecast/get',
            method: 'POST',
            data: {
                id: castReceiverManager.getApplicationData().sessionId,
                token: mediaInformation.contentId
            }
        }).done((data) => {
            let item = data.data;
            item.duration = duration;
            item.position = position;
            Chromecast.setTopPreview(item);

            Chromecast.media.animate({
                top: '100px',
                left: '30px',
                width: mediaWidth + 'px',
                height: mediaHeight + 'px',
            }, 3000, () => {
                Chromecast.animatePreview();
            });
            Chromecast.timeline.animate({
                width: '75%',
            }, 3000);
        });
    });
    playerManager.addEventListener(cast.framework.events.EventType.TIME_UPDATE, () => {
        const mediaInformation = playerManager.getMediaInformation();
        Chromecast.savePosition();

        if (
            playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED ||
            mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.AUDIO
        ) {
            const duration = parseInt(mediaInformation.duration);
            const position = parseInt(playerManager.getCurrentTimeSec());

            Chromecast.timelineDuration.html(duration.toTimeFormat());
            Chromecast.timelineCurrentPosition.html(position.toTimeFormat());
            Chromecast.timelineCurrentPosition.css('width', ((100 / duration) * position) + '%');
            Chromecast.timelinePosition.css('width', ((100 / duration) * position) + '%');
        }
    });
    playerManager.addEventListener(cast.framework.events.EventType.ENDED, () => {
        Chromecast.media.css('display', 'none');
        Chromecast.media.css('top', '100px');
        Chromecast.media.css('left', '30px');
        Chromecast.media.css('width', mediaWidth + 'px');
        Chromecast.media.css('height', mediaHeight + 'px');
        Chromecast.timeline.css('width', '75%');

        Chromecast.loadList(() => {
            Chromecast.animatePreview()
        });
    });

    window.setInterval(() => {
        let date = new Date();
        let hour = date.getHours();
        let minute = date.getMinutes();

        if (hour < 10) {
            hour = '0' + hour;
        }

        if (minute < 10) {
            minute = '0' + minute;
        }

        Chromecast.time.html(hour + ':' + minute);
    }, 800);
}