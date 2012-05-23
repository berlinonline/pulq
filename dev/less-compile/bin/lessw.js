/**
 * lessw.js serves as the command line entry point for the less_compile dev tool.
 * It takes two directory paths.
 * The first one shall point to a directory containing the less-css files to be compiled.
 * The second shall point to a location where the compiled css shall be deployed to.
 * The structure inside the provided less directory will then be mirrored to the supplied css deploy path.
 *
 * Usage example:
 * node bin/cli.js ../../pub/less ../../pub/css
 */
var app = require('../lib/App.js');
app.run();
