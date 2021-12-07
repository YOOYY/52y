var superagent = require('superagent'),
    config = require("../config");

function payment(req, res, url) {
    var data = req.body,
        nres = res,
        sreq = superagent.post(url);
    sreq.set({
        'Content-Type': 'application/x-www-form-urlencoded'
    })
    if (data) {
        sreq.send(data)
    }
    sreq.end(function (err, res) {
        if (err) {
            result = {
                "message": err
            };
            nres.send(result)
        } else {
            //格式化为json
            if (JSON.stringify(res.body) == "{}") {
                var text = res.text;
                if (text.charAt(0) == "{" && text.charAt(text.length - 1) == "}") {
                    result = JSON.parse(text);
                } else {
                    result = {
                        "data": text,
                        "message": "充值错误," + text
                    };
                }
            } else {
                result = res.body;
            }
            nres.json(result);
        }
    });
}

module.exports = payment;