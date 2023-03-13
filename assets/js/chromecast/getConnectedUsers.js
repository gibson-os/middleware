Chromecast.connectedUsers = {};
Chromecast.connectedUserIds = {};
Chromecast.getConnectedUsers = () => {
    let newConnectedUsers = {};
    let newConnectedUserIds = {};
    let connectedUserObjects = [];
    const castReceiverManager = cast.framework.CastReceiverContext.getInstance();
    const senders = castReceiverManager.getSenders();

    for (let i = 0; i < senders.length; i++) {
        if (!Chromecast.connectedUsers[senders[i].id]) {
            continue;
        }

        newConnectedUsers[senders[i].id] = Chromecast.connectedUsers[senders[i].id];
        newConnectedUserIds[senders[i].id] = Chromecast.connectedUserIds[senders[i].id];
        connectedUserObjects.push({
            userId: Chromecast.connectedUserIds[senders[i].id],
            senderId: senders[i].id,
            sessionId: castReceiverManager.getApplicationData().sessionId
        });
    }

    if (
        Object.keys(newConnectedUsers).length === 0 &&
        Object.keys(Chromecast.connectedUsers).length > 0
    ) {
        const senderId = Object.keys(Chromecast.connectedUsers)[0];

        newConnectedUsers[senderId] = Chromecast.connectedUsers[senderId];
        newConnectedUserIds[senderId] = Chromecast.connectedUserIds[senderId];
    }

    Chromecast.connectedUsers = newConnectedUsers;
    Chromecast.connectedUserIds = newConnectedUserIds;

    return JSON.stringify(connectedUserObjects);
}