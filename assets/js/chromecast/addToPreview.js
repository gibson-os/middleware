Chromecast.addToPreview = (item) => {
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
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
            nextFilesString = '<img src="/img/svg/warning.svg" class="warning" alt="warning" /> ';
        }

        nextFilesString += '+' + item.nextFiles;
    }

    Chromecast.previewItems.push(
        '<li>' +
            '<div class="previewImage" style="background-image: url(\'/middleware/chromecast/image/id/' + castReceiverManager.getApplicationData().sessionId + '/token/' + item.html5MediaToken + '/image.jpg?width=' + Chromecast.footer.width() + '\');">' +
                '<div class="previewTitle">' + item.filename + '</div>' +
                '<div class="previewNextFiles">' + nextFilesString + '</div>' +
            '</div>' +
            '<div class="previewTimeline"' + timelineStyle + '>' +
                '<div class="previewPosition" style="width: ' + ((100 / item.duration) * item.position) + '%;"></div>' +
            '</div>' +
        '</li>'
    );
}