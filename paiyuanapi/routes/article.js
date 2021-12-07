var express = require('express');
var etag = require('etag');
var router = express.Router();
//定时器
var schedule = require('node-schedule');
//缓存
const NodeCache = require("node-cache");
const myCache = new NodeCache({ stdTTL: 43200, errorOnMissing: true });

var article = require("../model/article.js");
var cache = require("../model/cache.js");

router.get('/:start/:limit', function (req, res) {
    var start = req.params.start,
        limit = req.params.limit,
        token;
    token = start + limit + 'list';
    try {
        result = myCache.get(token, true);
        checkEtag(result, req, res);
    } catch (err) {
        article.list(start, limit)
        .then(function(result){
            checkEtag(result, req, res)
            myCache.set(token, result);
        });
    }
})

router.get('/count', function (req, res) {
    var token = 'count';
    try {
        result = myCache.get(token, true);
        checkEtag(result, req, res);
    } catch (err) {
        article.count().then(function (result) {
            checkEtag(result, req, res)
            myCache.set(token, result);
        });
    }
})

//清理指定缓存
router.get('/del/:token', function (req, res) {
    var token = req.params.token;
    myCache.del(token);
    res.send(token + '删除成功');
    //myCache.flushAll()
})

//更新文件缓存
router.get('/update', function (req, res) {
    cache();
    res.send('更新成功');
})

router.get('/info/:id(\\d+)/:start(\\d+)', function (req, res) {
    var id = req.params.id,
        start = req.params.start,
        token;
    token = id + 'info';
    try {
        result = myCache.get(token, true);
        checkEtag(result, req, res);
    } catch (err) {
        article.info(id,start,8).then(function (result) {
            checkEtag(result, req, res)
            myCache.set(token, result);
        });
    }
})

router.get('/image', function (req, res) {
    var token = 'image';
    try {
        result = myCache.get(token, true);
        checkEtag(result, req, res);
    } catch (err) {
        article.image(0,20).then(function (result) {
            checkEtag(result, req, res)
            myCache.set(token, result);
        });
    }
})

function checkEtag(result, req, res) {
    var jresult = JSON.stringify(result);
    var hash = etag(jresult);
    var noneMatch = req.headers['if-none-match'];
    if (hash === noneMatch) {
        res.writeHead(304, "Not Modified");
        res.end();
    } else {
        res.header("ETag", hash);
        res.json(result);
    }
}

var rule = new schedule.RecurrenceRule();
rule.hour = 0; rule.minute = 0; rule.second = 0;
// rule.second = [10,20,30,40,50];
schedule.scheduleJob(rule, function () {
    cache();
    console.log('现在时间：', new Date());
});

//result = [{title:'少侠留步，玩测试小游戏还有好礼相送',date:'1541840390',href:''},{title:'少侠留步，玩测试小游戏还有好礼相送',date:'1541840390',href:''}];

module.exports = router;
