Chromecast.setTopPreview = (item, percentPerSecond = 0) => {
    Chromecast.title.html(item.filename);

    if (item.status === 'generate') {
        Chromecast.timelineBar.css('width', item.convertPercent + '%');
        Chromecast.timelineDuration.html(item.duration.toTimeFormat());
        Chromecast.timelineCurrentPosition.html(item.convertTimeRemaining.toTimeFormat());
        Chromecast.timelineCurrentPosition.css('width', parseInt(item.convertPercent) + '%');
        Chromecast.timelinePosition.css('width', '0%');

        window.setTimeout(() => {
            if (percentPerSecond === 0) {
                percentPerSecond = (100 - item.convertPercent) / item.convertTimeRemaining;
            }

            if (item.convertTimeRemaining <= 1) {
                Chromecast.getItem(item.token, (newItem) => {
                    Chromecast.setTopPreview(newItem, percentPerSecond);
                });

                return;
            }

            item.dureration += 1;
            item.convertPercent += percentPerSecond;
            item.convertTimeRemaining -= 1;

            Chromecast.setTopPreview(item, percentPerSecond);
        }, 3000);
    } else {
        Chromecast.timelineBar.css('width', '100%');
        Chromecast.timelineDuration.html(item.duration.toTimeFormat());
        Chromecast.timelineCurrentPosition.html(item.position.toTimeFormat());

        if (item.duration > 0) {
            Chromecast.timelineCurrentPosition.css('width', ((100 / item.duration) * item.position) + '%');
            Chromecast.timelinePosition.css('width', ((100 / item.duration) * item.position) + '%');
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

    Chromecast.nextFiles.html(nextFilesString);
    Chromecast.image.css(
        'background-image',
        'url(\'/middleware/chromecast/image' +
        '/id/' + cast.framework.CastReceiverContext.getInstance().getApplicationData().sessionId +
        '/token/' + item.html5MediaToken +
        '/image.jpg?width=' + Chromecast.image.width() + '\')'
    );
};