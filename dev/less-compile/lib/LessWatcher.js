var less = require("less");
var util = require("util");
var filesystem = require('fs');
var path = require('path');
var LessCompiler = require('./LessCompiler.js');

var LessWatcher = function(less_dir, css_dir, filter)
{
    this.less_dir = less_dir;
    this.css_dir = css_dir;
    this.filter = filter || '.less$';
    this.watched_files = {};
    this.inotify_timeouts = {};
    this.inotify_delay = 300;
};

LessWatcher.prototype.startWatching = function(callback)
{
    LessCompiler.walk(this.less_dir, this.filter, function(err, files)
    {
        files.forEach(function(file)
        {
            this.watchFile(file);
        }.bind(this));
    }.bind(this));
};

LessWatcher.prototype.stopWatching = function()
{

};

LessWatcher.prototype.isWatchingFile = function(filepath)
{
    return this.watched_files[filepath] || false;
};

LessWatcher.prototype.watchFile = function(less_file)
{
    if (!this.isWatchingFile(less_file))
    {
        var watch_options =  {
            interval: 100,
            persistent: true
        };

        filesystem.watchFile(less_file, watch_options, function(curr, prev)
        {
            if (curr.mtime.valueOf() != prev.mtime.valueOf() || curr.ctime.valueOf() != prev.ctime.valueOf())
            {
                this.clearEventTimeout('modified');
                this.setEventTimeout('modified', function()
                {
                    var css_file = less_file.replace(this.less_dir, this.css_dir).replace('.less', '.css');
                    LessCompiler.compileFile(less_file, css_file);
                }.bind(this));
            }
        }.bind(this));

        this.watched_files[less_file] = true;
    }
};

LessWatcher.prototype.clearEventTimeout = function(event_name)
{
    if (this.inotify_timeouts[event_name])
    {
        clearTimeout(this.inotify_timeouts[event_name]);
        this.inotify_timeouts[event_name] = null;
    }
};

LessWatcher.prototype.setEventTimeout = function(event_name, callback)
{
    this.inotify_timeouts[event_name] = setTimeout(
        function()
        {
            clearTimeout(this.inotify_timeouts[event_name]);
            this.inotify_timeouts[event_name] = null;
            callback();
        }.bind(this),
        this.inotify_delay
    );
};

exports.create = function(dir, filter)
{
    return new LessWatcher(dir, filter);
}