var gulp = require("gulp"),
    fs = require('fs'),
    git = require('gulp-git'),
    config = require('./config.js'),
    gitinit = false,
    gitfix = ((process.argv[2] == 'gitfix') ? true : false),
    gitLog = (process.argv[3] || (config.condition?'build':'fixed bugs'));

gulp.task('gitInit', function (done) {
    if (fs.existsSync('.git')) {
        console.log('init运行了');
        done();
    } else {
        gitinit = true;
        return git.init(function (err) {
            if (err) throw err;
        });
    }
})

gulp.task('gitAddremote', function (done) {
    if (gitinit) {
        return git.addRemote('origin', config.gitUrl, function (err) {
            if (err) throw err;
        });
    } else {
        console.log('addremote运行了');
        done();
    }
});

gulp.task('gitPull', function (done) {
    if (gitinit && !gitfix) {
        done();
    } else {
        console.log('pull运行了');
        return git.pull('origin', 'master', { args: '--rebase' }, function (err) {
            if(err){
                var error = 'pull出错！err:' + err + ';可以尝试运行git pull origin master指令';
                throw error;
            }
        });
    }
});

gulp.task('gitAdd', function () {
    return gulp.src(config.git)
        .pipe(git.add())
});

gulp.task('gitCommit', function () {
    return gulp.src(config.git)
        .pipe(git.commit(gitLog))
});

gulp.task('gitPush', function (done) {
    git.push('origin', 'master',function (err) {
        if (err) throw err;
    });
    done();
});

gulp.task('git', gulp.series('gitInit','gitAddremote', 'gitAdd', 'gitCommit', 'gitPull', 'gitPush'));
//如果gitfix时文件发生冲突,解决冲突后需要gulp gitadd,然后git rebase --continue
gulp.task('gitfix', gulp.series('gitInit','gitAddremote','gitAdd', 'gitCommit', 'gitPull', 'gitPush'));