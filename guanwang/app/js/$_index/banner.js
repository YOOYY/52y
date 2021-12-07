$(function() {
    var arr = [];
    for(var i=0;i<5;i++){
        var oImg = aLi(i).children().children('img');
        arr.push( [aLi(i).css('left'),aLi(i).css('top'),aLi(i).css('opacity'),aLi(i).css('zIndex') , oImg.css('width') ] );
    }

    $(".banner_box .prev").click(prev);
    $(".banner_box .next").click(next);

    $(".banner_box").hover(function(){
        clearInterval(timer);
    },function(){
        timer = setInterval(next,2000);
    })

    function aLi(i){
        return $('.banner_box ul li').eq(i);
    }

    function prev(){  //右
		arr.unshift(arr[arr.length-1]);
		arr.pop();
        move();
    }

    function next(){  //左
        arr.push(arr[0]);
        arr.shift();
        move();
    }

    function move(){

        for(var i=0;i<5;i++){
            
            var oImg = aLi(i).children().children('img'),item = arr[i];
                
                aLi(i).css('zIndex',item[3]);
                
                aLi(i).finish().animate({left : item[0] , top : item[1] , opacity : item[2] });
                
                oImg.finish().animate({ width : item[4] } );
                
            }
    }

    //自动播放
    var timer = setInterval(next,2000);
})