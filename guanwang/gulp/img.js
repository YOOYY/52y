var gulp = require("gulp"),
    path = require('path'),
    merge = require('merge-stream'),
    clean = require('gulp-clean'),
    gulpif = require('gulp-if'),

    reload = require('browser-sync').reload,

    spritesmith = require('gulp.spritesmith'),
    tinypng = require('gulp-tinypng'),

    rev = require('gulp-rev'),
    config = require('./config.js'),

    folders = [];

//复制文件夹作为操作文件夹
gulp.task('copyImgdir', function () {
    folders = config.getFolders(config.imgPath, config.prefix);
    return gulp.src(config.imgPath + '/**/*')
        .pipe(gulp.dest(config.outimgPath))
        .pipe(gulpif(
            !folders.length,reload({ stream: true })
        ));
})

//将带有$_前缀的文件夹中的png文件合并成雪碧图
gulp.task('sprites', function (done) {
    folders = config.getFolders(config.outimgPath, config.prefix);

    if (folders.length == 0) {
        done();
    }

    var tasks = folders.map(function (folder) {
        var str = folder.replace(config.outimgPath.replace('/', '\\'), ''),
            namedir = str.substr(0, str.lastIndexOf('\\') + 1),
            name = str.substr(str.search(config.prefix) + str.match(config.prefix)[0].length);
        return gulp.src(path.join(folder, '/*.{png,jpg}'))
            .pipe(spritesmith({
                imgName: '.' + namedir + name + '.png',//保存合并后图片的地址
                cssName: '../../' + config.scssPath + '/_sprites/' + name + '.scss',//保存合并后对于css样式的地址
                imgPath:(namedir + name + '.png').replace(/\\/g,'/').substr(1), // 手动指定路径, 会直接出现在background属性的值中
                padding: 5,//合并时两个图片的间距
                algorithm: 'binary-tree',//生成模式
                cssFormat:'scss',
                cssTemplate: config.scssPath + "/_sprites/template.handlebars",
                cssHandlebarsHelpers:{
                    'rename':function (path,name) {
                        return path.slice(0,-4).replace('/','_')+'_'+name;
                    }
                }
            }))
            .pipe(gulp.dest(config.outimgPath));
    });
    return merge(tasks);
})

gulp.task('cleanSprites', function (done) {
    if (folders.length == 0) {
        done();
    } else {
        return gulp.src(folders).pipe(clean())
        .pipe(reload({ stream: true }));
    }
})

//有损压缩图片
gulp.task('tinypng', function (done) {
    if (config.condition) {
        return gulp.src(config.outimgPath + '/**/*.{png,jpg}')
            .pipe(tinypng('L8AJPOaiMhx8bO2jozWdWi4T5WZIZiSk'))
    } else {
        done();
    }
});

gulp.task('imgrev', (done) =>{
    if (config.condition) {
        return gulp.src([config.outimgPath + '/**/*'])
        .pipe(rev())
        .pipe(rev.manifest({ merge: true }))
        .pipe(gulp.dest(config.outrevPath))
    } else {
        done();
    }
})

gulp.task('img', gulp.series('copyImgdir', 'sprites', 'cleanSprites', 'tinypng','imgrev'));