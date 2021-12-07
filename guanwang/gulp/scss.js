var gulp = require("gulp"),
    gulpif = require('gulp-if'),
    sass = require('gulp-sass')(require('sass')),
    cleanCSS = require('gulp-clean-css'),
    base64 = require('gulp-base64'),
    hash = require('gulp-css-assets-hash'),
    sassVariables = require('gulp-sass-variables'),

    postcss = require('gulp-postcss'),
    postcss2 = require('postcss');
    opacity = require('postcss-opacity'),
    pe = require('postcss-pseudoelements'),
    gradient = require('postcss-filter-gradient'),
    autoprefixer = require('autoprefixer'),
    px2rem = require('postcss-px2rem'),
    pixrem  = require('pixrem'),
    rev = require('gulp-rev'),
    reload = require('browser-sync').reload,
    config = require("./config.js");

//css
gulp.task('scss', function () {
    var processors = [
        opacity({legacy: true}),
        autoprefixer({overrideBrowserslist: config.browsers}),
        pe,
        gradient,
        px2rem({                    //处理SASS或LESS时，只能使用/*...*/注释，以便持续存在注释
            baseDpr: 2,             // 基本设备像素比 (default: 2)
            threeVersion: false,    // 是否生成@1x,@2x和@3x版本 (default: false)
            remVersion: true,       // 是否生成rem版本 (default: true)
            remUnit: 100,            // px:1rem (default: 100)
            remPrecision: 6         // rem值精度 (default: 6)
        }),
        pixrem({
            rootValue: 100,         //可以px，rem，em，%，或无单位像素值
            replace: false, 
            atrules: true, 
            html: false, 
            browsers: config.browsers, 
            unitPrecision: 6
        }),
        iepie
    ];

    return gulp.src(config.scssPath + '/*.scss')
        .pipe(sassVariables({
            $env: config.condition ? 'build' : 'debug'
        }))
        .pipe(sass().on('error', sass.logError).on('error',function(){
            throw('scss转css错误');
        }))
        // .pipe(postcss(processors))
        // .pipe(base64({
        //     extensions: ['svg', /\.jpg#datauri$/i],
        //     exclude:    [],
        //     maxImageSize: 8*1024,
        //     debug: true
        // }))
        // .pipe(styleLint({
        //     failAfterError: true,
        //     // 输出结果
        //     reporters: [
        //         { formatter: 'verbose', console: true },
        //     ]
        // }))
        // .pipe(cleanCSS({
        //     compatibility: 'ie8',
        //     level: {
        //         1: {
        //             roundingPrecision: 3
        //         },
        //         2: {
        //             restructureRules: true,
        //             removeUnusedAtRules:true,
        //             mergeSemantically:true
        //         }
        //     },
        //     format: (config.condition?'':'beautify')
        // }))
        // .pipe(
        //     gulpif(
        //         config.condition, rev()
        //     )
        // )
        // .pipe(hash())
        .pipe(gulp.dest(config.outcssPath))
        // .pipe(
        //     gulpif(
        //         config.condition, rev.manifest({ merge: true })
        //     )
        // )
        // .pipe(
        //     gulpif(
        //         config.condition, gulp.dest(config.outrevPath)
        //     )
        // )
        .pipe(reload({ stream: true }));
});

//清理无用css
// gulp.task('uncss', function(done) {
//     if (config.condition) {
//         return gulp.src(config.outcssPath + '/**/*.css')
//             .pipe(uncss({
//                 html: [config.outhtmlPath + '/**/*.html']
//             }))
//             .pipe(gulp.dest(config.outcssPath));
//     } else {
//         done();
//     }
// });

gulp.task('css', gulp.series('scss'))

const iepie = postcss2.plugin('iepie', function (opts) {
    opts = opts || {url:'../css/PIE.htc'};
    return function (css) {
        css.walkRules(function (rule) {
            var flag = false;
            rule.walkDecls(function(decl) {
                if (decl.type !== 'decl') { return; }
                if(flag){return;}
                var prop = decl.prop,
                    value = decl.value,
                    isradius = (prop.indexOf('border-radius') !== -1),
                    isshadow = (prop.indexOf('box-shadow') !== -1),
                    isrgba = (value.indexOf('rgba') !== -1);
                if(isrgba){
                    rule.append({            
                        prop: '-pie-'+prop,            
                        value: value          
                    });
                }
                if (isradius || isshadow) {
                    rule.append({            
                        prop: 'behavior',            
                        value: 'url('+ opts.url +')'          
                    });
                    flag = true;
                }
            });
        });
    };
}); 