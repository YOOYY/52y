$("#download .down_phone").hover(function(){
    $(this).parent().next().show();
},function(){
    $(this).parent().next().hide();
})

//news
$(function() {
    var pageid = $('body').data('pageid');
    switch (pageid) {
        case 'news':
            getList("#news .articlelist",0,8);
            getCount(function(count){
                $("#page").paging({
                    nowPage: 1, // 当前页码
                    pageNum: Math.ceil(count/8), // 总页码
                    total:count,
                    buttonNum: 10, //要展示的页码数量
                    canJump: 0,// 是否能跳转。
                    showOne: 1,//只有一页时，是否显示。0=显示（默认）,1=不显示
                    callback: function(num) { //回调函数
                        getList("#news .articlelist",num-1,8);
                    }
                });
            })
            break;
        case 'newsarticle':
            var id = GetQueryString('id'),start = GetQueryString('start');
            getArticle(id,start);
            break;
        case 'customer':
            var name = GetQueryString('name');
            var Articleurl = dataUrl+ name +'.json',type = 'customer';
            getStaticArticle(Articleurl,type);
            break;
        case 'introduction':
            var type = 'introduction';
            $("#gameList a").click(function(){
                $(this).children().addClass('active');
                $(this).siblings().children().removeClass('active')
                var name = $(this).children().data('name'), Articleurl = dataUrl+ name +'.json';
                getStaticArticle(Articleurl,type);
            })
            var index = GetQueryString('index') || 0;
            $("#gameList a").eq(index).click();
            break;
        default:
            break;
    }
})