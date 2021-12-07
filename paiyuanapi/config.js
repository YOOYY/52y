var env = process.env.NODE_ENV;
if (env == 'production' || (typeof env) == 'undefined') {
    var paybackUrl = 'http://test.com';
    CACHEURL = '/guanwang/html/cache/';
} else {
    var paybackUrl = 'http://test.com';
    CACHEURL = '../guanwang/debug/html/cache/';
}

const articleImgUrl = 'http://resources.52y.com/newpygame/',
    articleUrl = 'http://services.52y.com:8080/web/notices/client_noticeslist',
    webPAYURL = paybackUrl + '/payment/dopay',
    unionPAYURL = paybackUrl + '/payment/dounionpay',
    alipayPAYURL = paybackUrl + '/payment/doaliios',
    wechatPAYURL = paybackUrl + '/payment/dowechatpay';

module.exports = {
    articleImgUrl,
    articleUrl,
    webPAYURL,
    alipayPAYURL,
    wechatPAYURL,
    unionPAYURL,
    CACHEURL
};