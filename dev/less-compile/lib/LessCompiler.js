var less = require("less");
var util = require("util");
var filesystem = require('fs');
var path = require('path');

var LessCompiler = {};
/**
 * Compile and deploy all *.less files inside the given less_dir to the given css_dir.
 */
LessCompiler.compileDirectory = function(less_dir, css_dir)
{
    LessCompiler.walkDir(less_dir, '.less$', function(err, results)
    {
        results.forEach(function(less_file)
        {
            var css_file = less_file.replace(less_dir, css_dir).replace('.less', '.css');
            LessCompiler.compileFile(less_file, css_file);
        });
    });
};
/**
 * Compile and deploy a given less file to the given target path.
 */
LessCompiler.compileFile = function(less_file, target_path)
{
    filesystem.readFile(less_file, function(err, less_code)
    {
        util.log(util.inspect(less_file));
        less.render(less_code.toString(), {
            paths: [path.dirname(less_file)]
        }, function(err, compiled_css)
        {
            if (err)
            {
                util.log("Error - LessCompiler.compileFile - Failed to parse " + less_file + "\n" + err.toString());
            }
            else
            {
                filesystem.writeFileSync(target_path, compiled_css);
                util.log("INFO - LessCompiler.compileFile - Compiled: " + less_file);
            }
        })
    });
};

/**
 * Recursively walk through the given directory and collect all files
 * that are not inside hidden directories and that match the given regexp filter.
 */
LessCompiler.walkDir = function(dir, filter, done)
{
    var results = [];

    filesystem.readdir(dir, function(err, list)
    {
        if (err)
        {
            done(err);
            return;
        }

        var pending = list.length;

        list.forEach(function(file)
        {
            var filename = file;
            file = dir + '/' + file;

            filesystem.stat(file, function(err, stat)
            {
                if (stat && stat.isDirectory())
                {
                    if (filename.indexOf('.') !== 0)
                    {
                        LessCompiler.walkDir(file, filter, function(err, res)
                        {
                            results = results.concat(res);
                        });
                    }
                }
                else if (!filter || filename.match(filter))
                {
                    results.push(file);
                }

                if (!--pending)
                {
                    done(null, results);
                }
            });
        });
    });
}

exports.compileDirectory = LessCompiler.compileDirectory;
exports.compileFile = LessCompiler.compileFile;
exports.walk = LessCompiler.walkDir;