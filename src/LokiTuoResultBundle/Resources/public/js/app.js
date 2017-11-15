/**
 * Uses the Response and puts the count inside the Badge
 * @param response The Response of getPlayerBadge or getUSerBadge
 * @param badgeSelector The selector of the Badge to set the text.
 * @see getPlayerBadge
 * @see getUserBadge
 */
var successCb = function (response, badgeSelector) {
    var badge = $(badgeSelector);
    badge.text(response.count + "");
    if (response.count <= 0) {
        badge.hide();
    } else {
        badge.show();
    }
};
var errCb = function (response, textStatus, errorThrown) {
    console.log("Fail Response", response);
    console.log("Status", textStatus, " error", errorThrown);
};

/**
 * Get the amount of messages for the player
 * @param id the Id of the player
 */
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

/**
 * Fetch the total amount of messages
 */
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

/**
 * Sends an Ajax Request to mark the message as Read
 * @param msgId the ID of the message
 */
var markMessageRead = function (msgId) {
    $.ajax({
        method: "GET",
        url: Routing.generate("loki.tuo.message.read", {id: msgId}),
        error: errCb,
        success: function (response) {
            console.log(response);
            getUserBadge();
            getPlayerBadge(response.player.id)
        }
    });


};

$('.message-alert').click(function () {
    //TODO Why is this not called on a newly added Item.
    console.log($(this));
    var msgId = $(this).data("msg-id");
    console.log(msgId);
    markMessageRead(msgId)
});
