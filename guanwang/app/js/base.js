//config
var baseUrl = '/',
     viewUrl = baseUrl + 'html/',
     cacheUrl = viewUrl + 'cache/', 
     apiUrl = (location.hostname === 'www.52y.com'?'http://www.52y.com:3000/':location.protocol+'//'+location.hostname+'/'),
     dataUrl = viewUrl + 'data/';

$("#downAndroid").hover(function(){
    $("#codeAndroid").show().siblings().hide();
},function(){
    $("#codeBox .qrcode").hide();
})

$("#downIos").hover(function(){
    $("#codeIos").show().siblings().hide();
},function(){
    $("#codeBox .qrcode").hide();
})

function getList(select,start,end){
     getJSON(cacheUrl+'list.json', function (data){
          listArr = data.splice(start,end);
          appendList(select,listArr,start);
     },function(){
          getlistError(select,start,end);
     })
}

function getlistError(select,start,end) {
     jQuery.support.cors = true;
     $(select).each(function(){
          var url = apiUrl+'article/'+start+'/'+end;
          var _that = this;
          $.ajax({
               'url': url,
               'type': 'get',
               'dataType': 'json',
               success: function (listArr) {
                    appendList(_that,listArr,start);
               }
          }); 
     })
}

function getCount(success){
     getJSON(cacheUrl+'count.json',
     function (data){
          success(data.count);
     },function() {
          jQuery.support.cors = true;
          var url = apiUrl+'article/count/';
          $.ajax({
               'url': url,
               'type': 'get',
               'dataType': 'json',
               success: function (count) {
                    success(count);
               }
          }); 
     })
}

// /count/
function appendList(select,listArr,start){
     var l=listArr.length,dom = '';
     for(var i = 0; i < l; i++){
          var article = listArr[i];
          dom +=
          '<li>\
               <a href="' + viewUrl + 'news/article.html?id='+ article.id +'&start='+ start +'">\
                    <p class="text">'+article.title+'</p>\
                    <span class="time">'+timestampToTime(article.starttime)+'</span>\
               </a>\
          </li>';
     }
     $(select).html(dom);
}

function getStaticArticle(url,type){
     getJSON(url, function (data){
          $('.article .title').html(data.title);
          $('.article .article_content').html(data.content);
          var nav = data.nav,title = '';
          if(nav){
               var navContent = '';
               if(nav.prev){
                    navContent += '<a class="fl" href="' + nav.prev.href + '" data-type="' + nav.prev.href + '">????????????:'+nav.prev.name + '</a>';
               }
               if(nav.next){
                    navContent += '<a class="fr" href="' + nav.next.href + '" data-type="' + nav.next.href + '">????????????:'+nav.next.name + '</a>';
               }
               $('.article .article_nav').html(navContent);
          }
          switch(type){
               case 'customer':
                    title += '<a href="'+ baseUrl +'">??????</a> &gt; <a href="'+viewUrl+'customer/index.html">????????????</a> &gt;' + data.title;
                    break;
               case 'introduction':
                    title += '????????????&gt;' + data.navtitle;
                    break;
          }
          $('#sideMain .title_sidemain').html(title);
     })
}

function getArticle(id,start){
     getJSON(cacheUrl+'list.json', function (data){
          $.each (data, function (i, item)  
          {
               if(item.id == id){
                    appendArticle(item);
               }
          });
     },function() {
          jQuery.support.cors = true;
          var id = GetQueryString("id"), url = apiUrl+'article/info/' + id + '/'+start;
          $.ajax({
               'url': url,
               'type': 'get',
               'dataType': 'json',
               success: function (data) {
                    appendArticle(data);
               }
          }); 
     })
}

function appendArticle(data){
     $('.article .title').html(data.title);
     $('.article .article_content').html(data.content);
     title = '<a href="/">??????</a>&gt;<a href="/html/news/index.html">????????????</a>&gt;' + data.title;
     $('#sideMain .title_sidemain').html(title);
}

function GetQueryString(name) {
     var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if (r != null) return r[2];
     return "";
}

//???????????????
function timestampToTime(timestamp) {
     var date = new Date(timestamp * 1000);//????????????10??????*1000???????????????13??????????????????1000
     var Y = date.getFullYear() + '-';
     var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
     var D = date.getDate() < 10 ? '0'+date.getDate() : date.getDate();
     return Y+M+D;
}

function getJSON(url,callback,error){
     $.ajax({
          url: url,
          success: callback,
          dataType: 'json',
          error:error
     });
}