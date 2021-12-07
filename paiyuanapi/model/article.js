var axios = require('axios'),
   config = require("../config");

   // {
   //    "error": "0",
   //    "message": "",
   //    "data": {
   //       "noticeslist": [
   //          {
   //             "id": "137",
   //             "title": "测试公告",
   //             "starttime": "1585584000",
   //             "endtime": "1743436799",
   //             "config": {
   //             "flag": "1",
   //             "contents": "此条为测试公告，当前为测试版本",
   //             "imagesname": "",
   //             "flagid": "0"
   //             }
   //          }
   //       ]
   //    },
   //    "total": "1"
   // }

function list(start, limit) {
      return axios.post(config.articleUrl, {
         start: start,
         limit: limit
       }).then(function({data}){
          if(data.error === '0'){
            var result = data.data.noticeslist.filter(function(item){
               return (item.config.flag === "1" || item.config.flag === '2');
            })
             return result;
          }else{
            return [];
          }
      }).catch(function(e){
         console.log(e);
         return [];
      });
};

function count() {
   return axios.post(config.articleUrl, {
      start: 0,
      limit: 500
    }).then(function({data}){
      if(data.error === '0'){
         var result = data.data.noticeslist.filter(function(item){
            return (item.config.flag === "1" || item.config.flag === '2');
         })
         return result.length;
      }else{
         return 0;
      }
   }).catch(function(e){
      console.log(e);
      return 0;
   });
};

function info(id = '',start,end) {
   var data = list(start,end),result = {title:'',content:''};
   return data.then(function(lists){
      for(var i = 0; i<lists.length;i++){
         if(id === lists[i].id){
            result.title = lists[i].title;
            if(lists[i].config.flag === "2"){
               result.content = '<img src="'+config.articleImgUrl+lists[i].config.imagesname+'">';
            }else{
               result.content = lists[i].config.contents;
            }
         }
      }
      return result;
   })
};

function image(start,limit) {
   var res = {image:''};
   return axios.post(config.articleUrl, {
      start: start,
      limit: limit
    }).then(function({data}){
      if(data.error === '0'){
         var arr = data.data.noticeslist, result = arr.some(function(item){
            return (item.config.flag === '2');
         });
         if(result){
            for(var i = 0;i<arr.length;i++){
               if(arr[i].config.flag === '2'){
                  res.image = config.articleImgUrl+arr[i].config.imagesname;
                  return res;
               }
            }
         }else if(data.total>(start+1)*20){
            image(start+1,20);
         }else{
            return res;
         }
      }else{
         return res;
      }
   }).catch(function(e){
      console.log(e);
      return [];
   });
};

exports.count = count;
exports.info = info;
exports.list = list;
exports.image = image;