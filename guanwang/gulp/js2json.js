var through = require('through2'); // a thin wrapper around node transform streams
var gutil = require('gulp-util'); // utilities for gulp plugins

const PLUGIN_NAME = 'gulp-js-to-json';

// Plugin level function (dealing with files)
module.exports = function () {
    // Creating a stream through which each file will pass
    return through.obj(function (file, enc, cb) {
        
        if (file.isNull()) {
            return cb(null, file);
        }

        if (file.isStream()) {
            this.emit('error', new gutil.PluginError(PLUGIN_NAME, 'Stream not supported'));
            return cb(null, file);
        }
        var jsonFile = require(file.path);

        var data = JSON.stringify(jsonFile);

        file.contents = Buffer.from(data);
        if (file.path) {
            file.path = gutil.replaceExtension(file.path, '.json');
        }

        cb(null, file);
    });
};
