var createError = require('http-errors');
var express = require('express');
var path = require('path');
var cookieParser = require('cookie-parser');
var logger = require('morgan');

var articleRouter = require('./routes/article');
var payRouter = require('./routes/pay');
var app = express();

app.all('*', function (req, res, next) {
  res.header("Access-Control-Allow-Origin", req.headers.origin);
  res.header("Access-Control-Allow-Headers", "X-Requested-With, Content-Type");
  res.header("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");
  // 允许证书 携带cookie
  res.header("Access-Control-Allow-Credentials", "true")
  next();
});

app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({extended: false}));
app.use(cookieParser());

app.use('/article', articleRouter);
app.use('/pay', payRouter);
app.get('/', function (req, res) {
  res.send('访问成功!');
})

// catch 404 and forward to error handler
app.use(function (req, res, next) {
  next(res.send(404));
});

// error handler
app.use(function (err, req, res, next) {
  // set locals, only providing error in development
  res.locals.message = err.message;
  console.log(err);
  res.locals.error = req.app.get('env') === 'development' ? err : {};
  
  // render the error page
  res.status(err.status || 500);
  res.send(err);
});

module.exports = app;
