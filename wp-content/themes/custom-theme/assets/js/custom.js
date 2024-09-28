$(document).ready(function(){
    var url = $("#iframeYoutube").attr('src');
    $("#videoplay").on('hide.bs.modal', function(){
        $("#iframeYoutube").attr('src', '');
    });
    $("#videoplay").on('show.bs.modal', function(){
        $("#iframeYoutube").attr('src', url);
    });
});



$(document).ready(function(){
    $(".infoicon").click(function(){
        $(".docinfo").addClass('open')
   });
    $(".closesidebar").click(function(){
        $(".docinfo").removeClass('open')
    });
});
