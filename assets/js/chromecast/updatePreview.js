Chromecast.updatePreview = () => {
    Chromecast.footerUl.append(Chromecast.previewItems.join(''));
    Chromecast.previewItems = [];
}