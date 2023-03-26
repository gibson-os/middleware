Chromecast.setTopPreview = (item, generateTimestamp = null) => {
    Chromecast.title.html(item.filename);

    if (Chromecast.topPreviewTimeout) {
        window.clearTimeout(Chromecast.topPreviewTimeout);
        Chromecast.topPreviewTimeout = null;
        Chromecast.topPreviewGenerateTimestamp
    }

    if (item.status === 'generate') {
        let convertPercent = item.convertPercent;
        let convertTimeRemaining = item.convertTimeRemaining;

        if (generateTimestamp !== null) {
            const timeDifference = (Date.now() - generateTimestamp) / 1000;
            const percentPerSecond = (100 - item.convertPercent) / item.convertTimeRemaining;

            convertPercent += percentPerSecond * timeDifference;
            convertTimeRemaining -= timeDifference;
        }

        Chromecast.timelineBar.css('width', convertPercent + '%');
        Chromecast.timelineDuration.html(item.duration.toTimeFormat());
        Chromecast.timelineCurrentPosition.html(convertTimeRemaining.toTimeFormat());
        Chromecast.timelineCurrentPosition.css('width', convertPercent + '%');
        Chromecast.timelinePosition.css('width', '0%');

        if (convertTimeRemaining <= 1) {
            Chromecast.getItem(item.html5MediaToken, (newItem) => {
                Chromecast.setTopPreview(newItem);
            });

            return;
        }

        if (generateTimestamp === null) {
            generateTimestamp = Date.now();
        }

        Chromecast.topPreviewTimeout = window.setTimeout(() => {
            Chromecast.setTopPreview(item, generateTimestamp);
        }, 100);
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