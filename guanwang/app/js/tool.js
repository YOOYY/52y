/**
 * @return String "下午2:51:59"
 */
function getCurTime() {
    var date = new Date();
    return date.toLocaleTimeString();
}

/**
 * @return String "2019/7/19"
 */
function getCurDate() {
    var date = new Date();
    return date.toLocaleDateString();
}

function randInt(low, high) {
    return Math.floor(Math.random()*(high-low) + low)
}

function isNumber(str){
    var n = Number(str);
    if (!isNaN(n)){
        return true;
    }else{
        return false;
    }
}

function isObj(obj){
    if(typeof obj=="object"){
        return true;
    }else{
        return false;
    }
}

function emptyFun(obj){
    var obj=obj;
    if(obj==""||obj==null||obj==undefined||obj=="null"||obj=="undefined"){
        return true;
    }else{
        return false;
    }
}

function unique(arr){
    var res = [arr[0]];
    for(var i = 1; i < arr.length; i++){
        var repeat = false;
        for(var j = 0; j < res.length; j++){
            if(arr[i] == res[j]){
                repeat = true;
                break;
            }
        }
        if(!repeat){
            res.push(arr[i]);
        }
    }
    return res;
}

function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return "";
}

function timestampToTime(timestamp) {
    var date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    var Y = date.getFullYear() + "-";
    var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1)) + "-";
    var D = date.getDate() + 1 < 10 ? "0" + date.getDate() : date.getDate();
    return Y + M + D;
}

function post(url, data, success) {
  jQuery.support.cors = true;
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function(res) {
      res = JSON.parse(res);
      if (res.error == 0) {
        success(res.data);
      } else {
        // confirmMask(res.message);
      }
    },
    error: function(e) {
    //   confirmMask("网络错误!");
    }
  });
}

//设置cookie
function SetCookie(name, value)//两个参数，一个是cookie的名字，一个是值
{
    var Days = 30; //此 cookie 将被保存 30 天
    var exp = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
//读取cookies 
function GetCookie(name)      
{
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) return unescape(arr[2]); return null;

}
//删除cookies 
function delCookie(name) 
{ 
    var exp = new Date(); 
    exp.setTime(exp.getTime() - 1); 
    var cval=getCookie(name); 
    if(cval!=null) 
        document.cookie= name + "="+cval+";expires="+exp.toGMTString(); 
}

function arrClone(arr){
    return [].concat(arr);
}

function addUnion(arr,val){
    var l = arr.length;
    for(var i=0;i<l;i++){
        if(arr[i] == val){
            return;
        }
    }
    return arr.push(val);
}

//两数组左差集
Vue.prototype.LeftExcept = (arr1, arr2) => {
    var res = [].concat(arr1);
    arr1.forEach(function(val, index) {
        if (arr2.has(val)) {
            res.splice(index,1);
        }
    });
    return res;
}

    //差集
Vue.prototype.arr_dive = (aArr,bArr) => {
    　　if(bArr.length==0){return aArr}
    　　var diff=[];
    　　var str=bArr.join("&quot;&quot;");
    　　for(var e in aArr){
    　　if(str.indexOf(aArr[e])==-1){
    　　　　diff.push(aArr[e]);
    　　　　}
    　　}
    　　return diff;
    }