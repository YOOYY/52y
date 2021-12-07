getJSON(cacheUrl+'image.json', function (data){
    $("#newsBanner").attr('src',data.image);
},function() {
    jQuery.support.cors = true;
    var url = apiUrl+'article/image';
    $.ajax({
        'url': url,
        'type': 'get',
        'dataType': 'json',
        success: function (data) {
            $("#newsBanner").attr('src',data.image);
        }
    }); 
})