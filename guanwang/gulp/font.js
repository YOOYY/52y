var fs = require('fs'),
    gulp = require("gulp"),
    merge = require('merge-stream'),
    iconfont = require('gulp-iconfont'),
    iconfontCss = require('gulp-iconfont-css'),
    config = require("./config.js");

gulp.task('font', function(){
    var runTimestamp = Math.round(Date.now()/1000);
    var tasks = fs.readdirSync(config.fontPath).map(function (file) {
        var fileName = file.slice(0,-4);
        return gulp.src([config.fontPath + '/' + file], {base: './'})
        .pipe(iconfontCss({
            fontName: fileName,
            path: config.scssPath + '/_fonts/template.txt',
            targetPath: '../../'+ config.scssPath + '/_fonts/' + fileName + '.scss' //相对于字体生成的路径
        }))
        .pipe(iconfont({
            fontName: file, // 具体设置见 https://github.com/nfroidure/svgicons2svgfont#svgicons2svgfontoptions
            prependUnicode: true,
            formats: ['ttf', 'eot', 'woff','woff2','svg'],
            timestamp: runTimestamp
        }))
        .on('glyphs', function(glyphs, options) {
            // console.log(glyphs, options);
        })
        .pipe(gulp.dest(config.outfontPath));
    });

    return merge(tasks);
});

//rev???