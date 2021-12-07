/**
 * Created by Administrator on 2017/3/13.
 */
const Mock = require('mockjs');
const querystring = require('querystring');
const url = require('url');
const data = [
    {
        "route":"/client/market_info",
        "data":{
            "data|30":[
                {"day_total": "@integer(0,100000)", "total": "@integer(0,100000)", "playerid": "@integer(100000,300000)", "nick_name": "@string('lower',4)"}
            ]
        }
    },
    {
        "route":"/client/market_draw",
        "data":{
            "data|20":[
                {"day_total": "@integer(0,100000)", "total": "@integer(0,100000)", "playerid": "@integer(100000,300000)", "nick_name": "@string('lower',4)"}
            ]
        }
    },
    {
        "route":"/client/draw_list",
        "data":{
            "data":{"draw_list|20":[
                {"createtime": "759883437303", "lottery": "@integer(0,100000)"}
            ]}
        }
    },
    {
        "route":"/client/market_list",
        "data":{
            "data|20":[
                {"day_total": "@integer(0,100000)", "total": "@integer(0,100000)", "playerid": "@integer(100000,300000)", "nick_name": "@string('lower',4)","type":"@integer(1,3)"}
            ]
        }
    }
];

function filterData(data,path,{start = 0, limit = 10, order = false, type = 1} = {}){
    // console.log(data);
    //特殊处理
    if(path == '/client/draw_list'){
        data = data.draw_list;
    }else{
        //筛选
        data = data.filter(function(item) {
            return item.type == type;
        })
        //排序
        if(order == 'day_total'){
            data = data.sort(function(a,b){
                return (b.day_total - a.day_total);
            });
        }else{
            data = data.sort(function(a,b){
                return (b.total - a.total);
            });
        }
    }

    //截取
    data = data.slice(start,limit);

    return data;
}

exports.data = function(){
    var arr = [];
    data.forEach(function(value,index){
        var object = {
            route:value.route,
            handle:function (req,res) {
                var str = '',query = {},ourl = url.parse(req.url, true),path = req.originalUrl;
                if(req.method == "GET"){
                    query = ourl.query;
                    respond(res,path,query,value,index);
                }else if(req.method == "POST"){
                    req.on('data', data => {
                        str += data;
                    })
                
                    req.on('end', () => {
                        query = querystring.parse(str);
                        respond(res,path,query,value,index);
                    })
                }

            }
        }
        arr.push(object);
    })
    return arr;
};

function respond(res,path,query,value,index){
    if(!global['mockData' + index]){
        global['mockData' + index] = {};
    }

    global['mockData' + index] = ( (JSON.stringify(global['mockData' + index]) == "{}")?Mock.mock(value.data):global['mockData' + index]);

    var resData = {
        "error":"0",
        "message":"0",
        "data":[]
    }
    resData.data = filterData(global['mockData' + index].data,path,query);

    res.writeHead(200,{"Content-type":"text/html;charset=UTF-8"});
    res.write(JSON.stringify(resData));
    res.end();
}