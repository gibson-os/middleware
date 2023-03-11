Number.prototype.toTimeFormat = function () {
    const sec_num = parseInt(this, 10);
    let hours = Math.floor(sec_num / 3600);
    let minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    let seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) {
        hours = '0' + hours;
    }

    if (minutes < 10) {
        minutes = '0' + minutes;
    }

    if (seconds < 10) {
        seconds = '0' + seconds;
    }

    return hours + ':' + minutes + ':' + seconds;
};

jQuery(function() {
    const media = jQuery('#media');
    const title = jQuery('#title');
    const timeline = jQuery('#timeline');
    const timelineBar = jQuery('#timeline .bar');
    const timelineDuration = jQuery('#timeline .duration');
    const timelinePosition = jQuery('#timeline .bar div.position');
    const timelineCurrentPosition = jQuery('#timeline .currentPosition');
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const playerManager = castReceiverManager.getPlayerManager();
    let connectedUsers = {};
    let connectedUserIds = {};

    playerManager.addEventListener(cast.framework.events.EventType.PLAYING, () => {
        title.html(playerManager.getMediaInformation().metadata.title);
    });

    castReceiverManager.addCustomMessageListener('urn:x-cast:net.itronom.gibson', (event) => {
        connectedUsers[event.senderId] = event.data.user;
        connectedUserIds[event.senderId] = event.data.user.id;

        showMessage('Willkommen ' + event.data.user.user + '!');
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
            loadList(
                playerManager.getPlayerState() === cast.framework.messages.PlayerState.IDLE ||
                playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED
            );
        });
    });

    playerManager.setMediaElement(media.get(0));
    castReceiverManager.start();

    const getConnectedUser = () => {
        let newConnectedUsers = {};
        let newConnectedUserIds = {};
        let connectedUserObjects = [];
        const senders = castReceiverManager.getSenders();

        for (let i = 0; i < senders.length; i++) {
            if (!connectedUsers[senders[i].id]) {
                continue;
            }

            newConnectedUsers[senders[i].id] = connectedUsers[senders[i].id];
            newConnectedUserIds[senders[i].id] = connectedUserIds[senders[i].id];
            connectedUserObjects.push({
                userId: connectedUserIds[senders[i].id],
                senderId: senders[i].id,
                sessionId: castReceiverManager.getApplicationData().sessionId
            });
        }

        if (
            Object.keys(newConnectedUsers).length === 0 &&
            Object.keys(connectedUsers).length > 0
        ) {
            const senderId = Object.keys(connectedUsers)[0];

            newConnectedUsers[senderId] = connectedUsers[senderId];
            newConnectedUserIds[senderId] = connectedUserIds[senderId];
        }

        connectedUsers = newConnectedUsers;
        connectedUserIds = newConnectedUserIds;

        return JSON.stringify(connectedUserObjects);
    };

    let setTopPreview = function(item)
    {
        title.html(item.filename);

        if (item.status === 'generate') {
            timelineBar.css('width', item.convertPercent + '%');
            timelineDuration.html(item.duration.toTimeFormat());
            timelineCurrentPosition.html(item.convertTimeRemaining.toTimeFormat());
            timelineCurrentPosition.css('width', item.convertPercent + '%');
            timelinePosition.css('width', '0%');
        } else {
            timelineBar.css('width', '100%');
            timelineDuration.html(item.duration.toTimeFormat());
            timelineCurrentPosition.html(item.position.toTimeFormat());

            if (item.duration > 0) {
                timelineCurrentPosition.css('width', ((100 / item.duration) * item.position) + '%');
                timelinePosition.css('width', ((100 / item.duration) * item.position) + '%');
            }
        }

        let nextFilesString = '';

        if (item.nextFiles) {
            if (
                item.duration > 0 &&
                item.duration === item.position
            ) {
                nextFilesString = '<img src="/img/svg/warning.svg" class="warning" alt="warning" /> ';
            }

            nextFilesString += '+' + item.nextFiles;
        }

        jQuery('#nextFiles').html(nextFilesString);
        const image = jQuery('#image');
        image.css(
            'background-image',
            'url(\'/middleware/chromecast/image' +
            '/id/' + castReceiverManager.getApplicationData().sessionId +
            '/token/' + item.html5MediaToken +
            '/image.jpg?width=' + image.width() + '\')'
        );
    };

    let addToPreview = function(item)
    {
        let nextFilesString = '';
        let timelineStyle = '';

        if (item.status === 'generate') {
            timelineStyle = ' style="width: ' + item.convertPercent + '%"';
        }

        if (item.nextFiles) {
            if (
                item.duration > 0 &&
                item.duration === item.position
            ) {
                nextFilesString = '<img src="/img/svg/warning.svg" class="warning" alt=""warning /> ';
            }

            nextFilesString += '+' + item.nextFiles;
        }

        jQuery('footer ul').append(
            '<li>' +
            '<div class="previewImage" style="background-image: url(\'/middleware/chromecast/image/id/' + castReceiverManager.getApplicationData().sessionId + '/token/' + item.html5MediaToken + '/image.jpg?width=' + jQuery('footer').width() + '\');">' +
            '<div class="previewTitle">' + item.filename + '</div>' +
            '<div class="previewNextFiles">' + nextFilesString + '</div>' +
            '</div>' +
            '<div class="previewTimeline"' + timelineStyle + '>' +
            '<div class="previewPosition" style="width: ' + ((100 / item.duration) * item.position) + '%;"></div>' +
            '</div>' +
            '</li>'
        );
    };

    let animatePreview = function()
    {
        if (
            playerManager.getPlayerState() !== cast.framework.messages.PlayerState.IDLE &&
            playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
        ) {
            return;
        }

        jQuery('footer ul').css('display', 'block');
        let firstLi = jQuery('footer li:first');

        firstLi.animate({
            marginTop: '-' + firstLi.height() + 'px',
        }, 5000, 'linear', function() {
            if (
                playerManager.getPlayerState() !== cast.framework.messages.PlayerState.IDLE &&
                playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
            ) {
                jQuery('footer ul').css('display', 'none');
                return;
            }

            let lastLi = jQuery('footer li:last');

            if (lastLi.offset().top <= jQuery(window).height()) {
                loadList();
            }

            firstLi.remove();
            animatePreview();
        });
    };

    let loadList = function(callAnimatePreview = false) {
        jQuery.ajax({
            url: '/middleware/chromecast/toSeeList',
            method: 'POST',
            data: {
                id: castReceiverManager.getApplicationData().sessionId
            },
        }).done(function (data) {
            jQuery.each(data.data, function(position, item) {
                if (
                    position === 0 &&
                    playerManager.getPlayerState() !== cast.framework.messages.PlayerState.PAUSED
                ) {
                    setTopPreview(item);
                } else {
                    addToPreview(item);
                }
            });

            if (callAnimatePreview) {
                animatePreview();
            }
        });
    };

    let lastPosition = 0;
    let requestActive = false;
    let savePosition = function() {
        if (requestActive) {
            return;
        }

        const position = parseInt(playerManager.getCurrentTimeSec());

        if (position === lastPosition) {
            return;
        }

        lastPosition = position;
        requestActive = true;

        jQuery.ajax({
            url: '/middleware/chromecast/setUsers',
            method: 'POST',
            data: {
                users: getConnectedUser(),
                sessionId: castReceiverManager.getApplicationData().sessionId
            },
            complete() {
                jQuery.ajax({
                    url: '/middleware/chromecast/savePosition',
                    method: 'POST',
                    data: {
                        token: playerManager.getMediaInformation().contentId,
                        position: position,
                    },
                    complete() {
                        setTimeout(function() {
                            requestActive = false;
                        }, 3000);
                    }
                });
            }
        });
    };

    let mediaWidth = media.width();
    let mediaHeight = media.height();

    playerManager.addEventListener(cast.framework.events.EventType.LOAD_START, () => {
        jQuery('footer li:first').stop();
        media.stop();
        media.css('backgroundSize', 'auto');
        media.css('backgroundImage', 'url(\'/img/loading.gif\')');
        media.css('display', 'block');
    });
    playerManager.addEventListener(cast.framework.events.EventType.ERROR, (event) => {
        debugOverlay(event);
    });
    playerManager.addEventListener(cast.framework.events.EventType.PLAYING, () => {
        const mediaInformation = playerManager.getMediaInformation();
        title.html(mediaInformation.metadata.title);
        const isVideo = mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.VIDEO;
        jQuery('footer ul').css('display', 'none');
        media.css('zIndex', isVideo ? 99999 : 99);
        media.css('backgroundSize', 'cover');
        media.css(
            'backgroundImage',
            isVideo
                ? 'none'
                : 'url(\'/middleware/chromecast/image' +
                  '/id/' + castReceiverManager.getApplicationData().sessionId +
                  '/token/' + mediaInformation.contentId +
                  '/image.jpg?width=' + jQuery(window).width() + '\')'
        );
        media.animate({
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
        }, 3000);

        if (!isVideo) {
            timeline.animate({
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

        media.stop();

        jQuery.ajax({
            url: '/middleware/chromecast/get',
            method: 'POST',
            data: {
                token: mediaInformation.contentId
            }
        }).done(function (data) {
            let item = data.data;
            item.duration = duration;
            item.position = position;
            setTopPreview(item);

            media.animate({
                top: '100px',
                left: '30px',
                width: mediaWidth + 'px',
                height: mediaHeight + 'px',
            }, 3000, () => {
                animatePreview();
            });
            timeline.animate({
                width: '75%',
            }, 3000);
        });
    });
    playerManager.addEventListener(cast.framework.events.EventType.TIME_UPDATE, () => {
        const mediaInformation = playerManager.getMediaInformation();
        savePosition();

        if (playerManager.getPlayerState() === cast.framework.messages.PlayerState.PAUSED || mediaInformation.mediaCategory === cast.framework.messages.MediaCategory.AUDIO) {
            const duration = parseInt(mediaInformation.duration);
            const position = parseInt(playerManager.getCurrentTimeSec());

            timelineDuration.html(duration.toTimeFormat());
            timelineCurrentPosition.html(position.toTimeFormat());
            timelineCurrentPosition.css('width', ((100 / duration) * position) + '%');
            timelinePosition.css('width', ((100 / duration) * position) + '%');
        }
    });
    playerManager.addEventListener(cast.framework.events.EventType.ENDED, () => {
        media.css('display', 'none');
        media.css('top', '100px');
        media.css('left', '30px');
        media.css('width', mediaWidth + 'px');
        media.css('height', mediaHeight + 'px');
        timeline.css('width', '75%');

        loadList(true);
    });

    window.setInterval(function() {
        let date = new Date();
        let hour = date.getHours();
        let minute = date.getMinutes();

        if (hour < 10) {
            hour = '0' + hour;
        }

        if (minute < 10) {
            minute = '0' + minute;
        }

        jQuery('#time').html(hour + ':' + minute);
    }, 800);
});

function debugOverlay(value)
{
    const debug = jQuery('#debug');
    debug.css('display', 'block');
    debug.html(JSON.stringify(value));
}

function showMessage(message, image)
{
    const messageContainer = jQuery('#messageContainer');
    let backgroundImage = 'none';

    if (image) {
        backgroundImage = 'url(\'' + image + '?width=' + messageImage.width() + '&height=' + messageImage.height() + '\')';
    }

    jQuery('#message').html(message);
    const messageImage = jQuery('#messageImage');
    messageImage.css('background-image', backgroundImage);
    messageContainer.css('opacity', 1);
    window.setTimeout(() => {
        messageContainer.css('opacity', 0);
    }, 2500);
}