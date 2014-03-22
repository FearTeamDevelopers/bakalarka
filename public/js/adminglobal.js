//$(document).ready(function() {
// $('.queueDiv .qUserWrapper .qButtons .qChangeStatus').click(function() {
//     var id = $(this).attr("value");
//     alert(id);
//        $.post("/admin/index/changeStatus/"+id,function(msg){
//            if(msg == active){
//                $(this).parent("div").addClass("qUserWrapperActive");
//                $(".chatWindow").show();
//            }else{
//                $(this).parent("div").remove();
//                $(".chatWindow").hide();
//            }
//        });
//    });
//});

setInterval(function() {
    $(".chatContent .messageWrapper").load("/admin/index/loadChat/");
    $(".queueDiv").load("/admin/index/loadQ/");
}, 5000);