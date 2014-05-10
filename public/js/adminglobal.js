jQuery(document).ready(function() {
    jQuery(window).load(scrollDown);

    jQuery('#chatTextarea').keydown(function() {
        jQuery.post('/admin/setWriting');
        setTimeout(function() {
            jQuery.post('/admin/setNotWriting');
        }, 5000);
    });

    jQuery('#konec').click(function(event) {

        var c = confirm('Opravdu chcete ukoncit konverzaci?');
        if (c) {
            jQuery.post('/admin/delete');
        }

        event.preventDefault();
    });
    jQuery('#hideChat').click(function() {
        var button = getElementById('hide');
        jQuery.toggle(button);
        alert("bla");
    });
});

scrollDown = function() {
    document.getElementById("messageWrapper").scrollTop = document.getElementById("messageWrapper").scrollHeight;
};

setInterval(function() {
    jQuery.post('/admin/userIsWriting', function(msg) {
        var c = msg.substr(0, 1);
        if (c == 1) {
            jQuery('#writingIndicator').show();
        } else {
            jQuery('#writingIndicator').hide();
        }
    });
    jQuery(".chatContent #messageWrapper").load("/admin/index/loadChat/");
    jQuery(".queueDiv").load("/admin/index/loadQ/");
    scrollDown();
    jQuery.post("/admin/index/playSound/", function(msg) {
        var c = msg.substr(0, 3);
        if (c == 'bla') {
            playAdminSound();
        }
    });
}, 1000);


function playAdminSound() {
    var audio = document.getElementById("adminSound");
    audio.play();
}