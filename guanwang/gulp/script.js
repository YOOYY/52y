var gulp = require("gulp"),
    merge = require('merge-stream'),
    gulpif = require('gulp-if'),
    path = require('path'),

    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    eslint = require('gulp-eslint'),
    clean = require('gulp-clean'),
    browserSync = require('browser-sync'),
    reload = browserSync.reload,

    rev = require('gulp-rev'),
    config = require('./config.js'),

    folders = [];

gulp.task('copyJsdir', function () {
    return gulp.src(config.jsPath + '/**/*')
        .pipe(gulp.dest(config.outjsPath))
})

gulp.task('jsConcat', function (done) {
    folders = config.getFolders(config.outjsPath, config.prefix);
    if (folders.length == 0) {
        done();
    }
    var tasks = folders.map(function (folder) {
        var str = folder.replace(config.outjsPath.replace('/', '\\'), ''),
            namedir = str.substr(0, str.lastIndexOf('\\') + 1),
            name = str.substr(str.search(config.prefix) + str.match(config.prefix)[0].length);
        return gulp.src(path.join(folder, '/*.js'))
            .pipe(concat(name + '.js'))
            .pipe(gulp.dest(config.outjsPath + namedir));
    });

    return merge(tasks);
});

gulp.task('cleanJs', function (done) {
    if (folders.length == 0) {
        done();
    } else {
        return gulp.src(folders).pipe(clean());
    }
})

gulp.task('eslint', function () {
    return gulp.src(config.outjsPath + '/**/*.js')
    // base64
        // .pipe(
        //     gulpif(
        //         config.condition, eslint({
        //             rules: {
        //                 "no-debugger": 2,
        //                 "no-console": 2
        //             },
        //             fix: true
        //         }),
        //         eslint()
        //     )
        // )
        // .pipe(eslint.format())
        // .pipe(eslint.failAfterError())
        .pipe(gulp.dest(config.outjsPath))
        .pipe(reload({ stream: true }));
});

gulp.task('jsUglify', (done) =>{
    if (config.condition) {
        return gulp.src([config.outjsPath + '/**/*'])
        .pipe(uglify({
            warnings:true,
            compress:{
                drop_console:true,
                keep_infinity:true,
                toplevel:true
            },
            mangle: {toplevel: true},
            toplevel:true,
            ie8:true
        }))
        .pipe(rev())
        .pipe(rev.manifest({ merge: true }))
        .pipe(gulp.dest(config.outrevPath))
    } else {
        done();
    }
})

gulp.task('jsrev', (done) =>{
    if (config.condition) {
        return gulp.src([config.outimgPath + '/**/*'])
        .pipe(rev())
        .pipe(rev.manifest({ merge: true }))
        .pipe(gulp.dest(config.outrevPath))
    } else {
        done();
    }
})

gulp.task('datarev', (done) =>{
    if (config.condition) {
        return gulp.src([config.outimgPath + '/**/*'])
        .pipe(rev())
        .pipe(rev.manifest({ merge: true }))
        .pipe(gulp.dest(config.outrevPath))
    } else {
        done();
    }
})

gulp.task('js', gulp.series('copyJsdir','jsConcat','cleanJs'));
gulp.task('es', gulp.series('jsUglify'));