var rootPath = 'app',
    imgPath = rootPath + "/imgs",
    pugPath = rootPath + "/pug",
    dataPath = rootPath + "/pug/data",
    scssPath = rootPath + "/scss",
    jsPath = rootPath + "/js",
    fontPath = rootPath + "/fonts",

    prefix = "\\$_",
    git = ["./app","README.md","*(debug|build)"],
    gitUrl = "http://10.1.3.188/paiyuan3d/guanwang.git",
    condition = (process.argv[2] === 'build' ? true : false),
    browsers = [
        "last 2 versions",
        "> 1% in CN",
        "ie >= 8",
        "maintained node versions",
        "not dead"
    ],

    outPath = (condition ? "build" : "debug"),
    outimgPath = outPath + "/imgs",
    outhtmlPath = outPath + "/html",
    outdataPath = outPath + "/html/data",
    outcssPath = outPath + "/css",
    outjsPath = outPath + "/js",
    outfontPath = outPath + "/fonts",
    outrevPath = outPath + "/rev",

    fs = require('fs'),
    path = require('path'),
    mock = require('../mock.js'),
    server = {
        baseDir: outPath,
        directory: false,
        index: ["index.html"],
        tunnel: true,
        middleware: mock.data()
    },

    getFolders = function(dir, prefix) {
        var dirArr = [];
        findfile(dir, dirArr, prefix);
        return dirArr;
    
        function findfile(dir, dirArr, prefix) {
            fs.readdirSync(dir)
                .map(function (file) {
                    var filePath = path.join(dir, file);
                    if (fs.statSync(filePath).isDirectory()) {
                        if (file.match(prefix)) {
                            dirArr.push(filePath);
                        } else {
                            findfile(filePath, dirArr, prefix);
                        }
                    }
                });
        }
    };
    
exports = module.exports = {
    rootPath,
    imgPath,
    pugPath,
    dataPath,
    scssPath,
    jsPath,
    fontPath,

    prefix,
    git,
    gitUrl,
    condition,
    browsers,

    outPath,
    outimgPath,
    outhtmlPath,
    outdataPath,
    outcssPath,
    outjsPath,
    outfontPath,
    outrevPath,
    getFolders,
    server
}