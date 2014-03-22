//function showDiv(){
//    if(document.getElementById('chatWrapper').style.display = "none"){
//    document.getElementById('chatWrapper').style.display="block";
//}else document.getElementById('chatWrapper').style.display="none";
//}

$(document).ready(function() {

    $(window).load(function() {
        $("#loader, .loader").hide();
        //$.post("/ajaxSubmit", {text: 'bla'});
    });

    /* GLOBAL SCRIPTS */

    $('#hide').click(function() {
        $('#chatWrapper').toggle(800);
    });

    $(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });


});


setInterval(function() {
    $(".chatContent .messageWrapper").load("/app/index/loadKonversation/");
}, 5000);