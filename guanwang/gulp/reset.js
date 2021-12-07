var gulp = require("gulp"),
    clean = require('gulp-clean'),
    config = require("./config.js"),
    fs = require('fs');

//清空除imgs之外的文件
gulp.task('cleanDir', function (done) {
    if (fs.existsSync(config.outPath)) {
        return gulp.src([config.outPath + '/**/*', '!' + config.outimgPath + '/**']).pipe(clean());
    } else {
        done();
    }
}) 

//清理rev版本文件
gulp.task('cleanRev', (done) => {
    if (config.condition) {
        return gulp.src(outrevPath).pipe(clean());
    } else {
        done();
    }
});