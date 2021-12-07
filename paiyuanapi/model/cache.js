var config = require("../config"),
    axios = require('axios'),
    fs = require('fs');

function write(type,val){
    val = JSON.stringify(val);
    if (val) {
        var writable = fs.createWriteStream(config.CACHEURL+type+'.json');
          
        writable.on('finish', function(){

        });
        
        writable.on('error', function(err){
            console.log('write error - %s', err.message);
        });
        
        writable.write(val, 'utf8');
        
        writable.end();
    }
}

function cache(){
    return axios.post(config.articleUrl, {
        start: 0,
        limit: 500
      }).then(function({data}){
         if(data.error === '0'){
            var result = [],image = '';
            data.data.noticeslist.forEach(function(item){
                //1是文本，2是图片
                if(item.config.flag === "1"){
                    let obj = {};
                    obj.id = item.id;
                    obj.title = item.title;
                    obj.content = item.config.contents;
                    obj.starttime = item.starttime;
                    result.push(obj);
                }else if(item.config.flag === '2'){
                    let obj = {};
                    obj.id = item.id;
                    obj.title = item.title;
                    obj.content = '<img src='+config.articleImgUrl+item.config.imagesname+'>';
                    obj.starttime = item.starttime;
                    result.push(obj);
                    if(!image){
                        image = config.articleImgUrl+item.config.imagesname;
                    }
                }
            })
            write('list',result);
            write('count',{count:result.length});
//            write('image',{image:image});
        }else{
            console.log(data);
        }
     }).catch(function(e){
        console.log(e);
     });
}

module.exports = cache;
