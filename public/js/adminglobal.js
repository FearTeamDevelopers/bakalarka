$(document).ready(function() {

   
    $(window).load(scrollDown);
    
});
scrollDown = function() {
   document.getElementById("messageWrapper").scrollTop = document.getElementById("messageWrapper").scrollHeight;
} ;


setInterval(function() {
    $(".chatContent #messageWrapper").load("/admin/index/loadChat/");
    $(".queueDiv").load("/admin/index/loadQ/");
     $(".chatContent #messageWrapper").load(scrollDown);
}, 5000);


