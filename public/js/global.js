//function showDiv(){
//    if(document.getElementById('chatWrapper').style.display = "none"){
//    document.getElementById('chatWrapper').style.display="block";
//}else document.getElementById('chatWrapper').style.display="none";
//}
jQuery.noConflict();

jQuery(document).ready(function() {

    jQuery.post('/checkUser', function(msg) {
        var c = msg.substr(0, 2);
        if (c == 'ok') {
            jQuery('#loginStep').hide();
            jQuery("#chatStep").show();
        } else {
            jQuery('#loginStep').show();
            jQuery("#chatStep").hide();
        }
    });
    jQuery('#chatTextarea').keydown(function() {
        jQuery.post('/setWriting');
        setTimeout(function() {
            jQuery.post('/setNotWriting');
        }, 5000);
    });

    /* GLOBAL SCRIPTS */

    jQuery('#hide').click(function() {
        jQuery('#chatWrapper').show(500);
        jQuery(this).hide(500);
    });

    jQuery(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

    jQuery('#inputForm').submit(function(event) {

        var formData = {
            'chatTextInput': jQuery('#inputForm #chatTextarea').val()
        };

        jQuery.ajax({
            type: 'POST',
            url: '/submitChat',
            data: formData,
            dataType: 'text'
        }).done(function(data) {
            jQuery('#chatTextarea').val('');
            jQuery(".chatContent #messageWrapper2").load("/loadConversation")
                    .delay(500)
                    .animate({scrollTop: jQuery('#clearence').offset().top}, 'fast');
        }).fail(function(data) {
            //alert(data);
        });

        event.preventDefault();
    });

    jQuery('#loginButton').click(function() {
        var firstName = jQuery('.login input[name=firstname]').val();
        var lastName = jQuery('.login input[name=lastname]').val();

        jQuery.post('/login', {firstName: firstName, lastName: lastName}, function(message) {
            if (message == 'success') {
                jQuery('#loginStep').hide();
                jQuery("#chatStep").show();
            } else {
                jQuery('#loginStep .errorBox').text(message);
            }
        })
    });
});

scrollDown = function() {
    document.getElementById("messageWrapper2").scrollTop = document.getElementById("messageWrapper2").scrollHeight;
};

setInterval(function() {
    jQuery('#loader, .loader').hide();

    jQuery.post('/adminIsWriting', function(msg) {
        var c = msg.substr(0, 1);
        if (c == 1) {
            jQuery('#writingIndicator').show();
        } else {
            jQuery('#writingIndicator').hide();
        }
    });

    jQuery(".chatContent #messageWrapper2")
            .load("/loadConversation")
            .animate({scrollTop: jQuery('#clearence').offset().top}, 'fast');

    var messageInput = jQuery(".chatInputs").is(":visible");

    if (messageInput == false) {
        jQuery.post("/checkStatus", function(msg) {
            if (msg == 1) {
                //location.reload(1);
                jQuery(".chatInputs").show();
                playActiveSound();
            } else {
                jQuery(".chatInputs").hide();
            }
        });
    } else {
        jQuery.post("/checkStatus", function(msg) {
            if (msg == 3) { //konversation inactive
                jQuery("#chatStep").show();
                jQuery(".chatInputs").hide();
            } else if (msg == 2) { //user deleted by admin
                jQuery("#chatStep").hide();
                jQuery('#loginStep').show();
            } else if (msg == 4) { //no user in session - user is not logged
                jQuery('#loginStep').show();
                jQuery("#chatStep").hide();
            }
        });
    }
}, 1000);



function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}
function playActiveSound() {
    var audio = document.getElementById("activeSound");
    audio.play();
}

