var gulp = require("gulp"),
    fs = require('fs'),
    gulpif = require('gulp-if'),
    data = require('gulp-data'),

    pug = require('gulp-pug'),
    pugLinter = require('gulp-pug-linter'),

    htmllint = require("gulp-htmllint"),
    fancyLog = require('fancy-log'),
    colors = require('ansi-colors'),

    js2json = require('./js2json'),

    htmlmin = require('gulp-htmlmin'),
    revCollector = require('gulp-rev-collector'),
    reload = require('browser-sync').reload,
    config = require('./config.js');

//html

gulp.task('pugLint', () =>
    gulp.src(config.pugPath + '/**/*.pug')
        .pipe(pugLinter({
            reporter: puglintReporter,
            failAfterError: true
        }))
);

gulp.task('pug', () =>
    gulp.src([config.pugPath + '/**/*.pug', '!' + config.pugPath + '/_share/**'])
        .pipe(
            gulpif(
                fs.existsSync(config.pugPath + '/_share/config/' + config.outPath + '.json'),data(function () {
                    return JSON.parse(fs.readFileSync(config.pugPath + '/_share/config/' + config.outPath + '.json'));
                })
            )
        )
        .pipe(data(function () {
            return JSON.parse(fs.readFileSync(config.pugPath + '/_share/config/index.json'));
        }))
        .pipe(pug({
            pretty: !config.condition,
            doctype: 'html'
        }))
        // .pipe(htmllint({"failOnError":true,"config":".htmllintrc.json"}, htmllintReporter))
        .pipe(gulp.dest(config.outhtmlPath))
        .pipe(reload({ stream: true }))
);

gulp.task('revCollector', (done) => {
    if (config.condition) {
        return gulp.src([config.outrevPath + '/**/*.json', config.outhtmlPath + '/**/*.html'])
            .pipe(revCollector())
            .pipe(
                htmlmin({
                    collapseWhitespace: true,
                    removeEmptyAttributes:true,
                    removeRedundantAttributes:true,
                    removeScriptTypeAttributes:true,
                    removeStyleLinkTypeAttributes:true,
                    sortAttributes:true,
                    sortClassName:true,
                    minifyCSS:true,
                    minifyJS:true
                })
            )
            .pipe(gulp.dest(config.outhtmlPath))
    } else {
        done();
    }
});

gulp.task('transformData', function () {
    return gulp.src(config.dataPath + '/*.js')
        .pipe(js2json())
        .pipe(gulp.dest(config.outdataPath));
});

gulp.task('html', gulp.series('pugLint', 'pug', 'transformData', 'revCollector'))

const puglintReporter = (errors) => {
    if (errors.length > 0) {
        errors.map(function (error) {
            fancyLog(colors.cyan('[gulp-puglint] ') + colors.white(error.filename + ' [' + error.line + ',' + error.column + ']: ') + colors.red('(' + error.code + ') ' + error.msg));
        });
 
        process.exitCode = 1;
    }
};

function htmllintReporter(filepath, issues) {
    if (issues.length > 0) {
        issues.forEach(function (issue) {
            fancyLog(colors.cyan('[gulp-htmllint] ') + colors.white(filepath + ' [' + issue.line + ',' + issue.column + ']: ') + colors.red('(' + issue.code + ') ' + issue.msg));
        });
 
        process.exitCode = 1;
    }
}