var express = require('express');
var config = require("../config");
var payment = require("../model/payment");
var router = express.Router();

router.post('/', function (req, res) {
    var url = config.webPAYURL;
    payment(req, res, url);
});

router.post('/alipay', function (req, res) {
    var url = config.alipayPAYURL;
    payment(req, res, url);
});

router.post('/wechat', function (req, res) {
    var url = config.wechatPAYURL;
    payment(req, res, url);
});

router.post('/union', function (req, res) {
    var url = config.unionPAYURL;
    payment(req, res, url);
});
/*
发送json,以表单方式post
{
    "account": this.form.name,
    "accountID": this.form.rename,
    "index": this.form.index,
    "type": wechat,
    "isExchange": this.form.exchange
}
接受json
{
    'ret' : 'success',
    'account' : 'infullAccount',
    'reaccount' : 'infullAccountID',
    'amount' : 'infullAmount',
    'code_url' : 'http://img.hb.aicdn.com/cd4c50d9fd37f2006a9a940c7519aab223789af8b447-hrXVT2_fw658'
}
 */
module.exports = router;