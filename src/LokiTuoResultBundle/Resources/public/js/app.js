var successCb = function (response, badgeSelector) {
    var badge = $(badgeSelector);
    badge.text(response.count + "");
    if (response <= 0) {
        badge.hide();
    } else {
        badge.show();
    }
};
var errCb = function (response, textStatus, errorThrown) {
    console.log("Fail Response", response);
    console.log("Status", textStatus, " error", errorThrown);
};
var getPlayerBadge = function (id) {

    $.ajax({
        method: "GET",
        url: Routing.generate("loki.tuo.message.count.player", {id: id}),
        error: errCb,
        success: function (response) {
            var badgeSelector = '#message-player-' + id;
            successCb(response, badgeSelector);
        }
    })
};

//Get the Total number of messages
var getUserBadge = function () {
    $.ajax({
        method: "GET",
        url: Routing.generate("loki.tuo.message.count.user"),
        error: errCb,
        success: function (response) {
            successCb(response, '#message-user');
        }
    });
};

var getMessagesForPlayer = function (playerId) {
    $.ajax({
        method: "GET",
        url: Routing.generate("loki.tuo.message.show", {id: playerId}),
        error: errCb,
        success: function (response) {
            var div = $('#messages');
            response.forEach(function (msg) {
                var child = '<div class="message" id="msg' + msg.id + '" ><small>' + msg.player.name + ':</small>' +
                    msg.message
                    + '</div>';
                div.append(child);
            });
            console.log(response);
        }
    });
};

var markMessageRead = function (msgId) {
    $.ajax({
        method: "GET",
        url: Routing.generate("loki.tuo.message.read", {id: msgId}),
        error: errCb,
        success: function (response) {
            console.log(response);
        }
    });
};