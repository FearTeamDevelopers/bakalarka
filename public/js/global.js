//function showDiv(){
//    if(document.getElementById('chatWrapper').style.display = "none"){
//    document.getElementById('chatWrapper').style.display="block";
//}else document.getElementById('chatWrapper').style.display="none";
//}

$(document).ready(function() {

    /* GLOBAL SCRIPTS */

    $('#hide').click(function() {
        $('#chatWrapper').show(500);
        $(this).hide(500);
    });

    $(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

    $('#inputForm').submit(function(event) {

        var formData = {
            'chatTextInput': $('#inputForm .chatTextarea').val()
        };

        $.ajax({
            type: 'POST',
            url: '/submitChat',
            data: formData,
            dataType: 'text'
        }).done(function(data) {
            $('#inputForm .chatTextarea').text('');
            $(".chatContent #messageWrapper2").load("/app/index/loadKonversation/")
                    .delay(500)
                    .animate({scrollTop: $('#clearence').offset().top}, 'fast');
        }).fail(function(data) {
            //alert(data);
        });

        event.preventDefault();
    });


    //$(window).load(scrollDown);

});

scrollDown = function() {
    document.getElementById("messageWrapper2").scrollTop = document.getElementById("messageWrapper2").scrollHeight;
};

setInterval(function() {

    $(".chatContent #messageWrapper2")
            .load("/app/index/loadKonversation/")
            .load(scrollDown);

    var messageInput = $(".chatInputs").is(":visible");
    
    if (messageInput == false) {
        $.post("/app/index/checkStatus/", function(msg) {
            if (msg == 1) {
                location.reload(1);

            } else {
                $(".chatInputs").hide();
            }
        });
    } else {
        $.post("/app/index/checkStatus/", function(msg) {
            if (msg == 3) { //konversation inactive
                $(".chatInputs").hide();
            } else if (msg == 2) { //user deleted by admin
                window.location = "/logout";
                
                //delete na strane admina a tady pak jen
                //location.reload(1);
            } else if (msg == 4) { //no user in session - user is not logged
                window.location = "/login";

                //location.reload(1);
            }
        });
        
    }

}, 5000);