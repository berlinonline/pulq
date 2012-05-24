#!/bin/bash
export PHP_COMMAND=/usr/bin/php
export BASE_HREF="http://localhost:8080/"

if (test -z "$AGAVI_ENVIRONMENT") ; then
   export AGAVI_ENVIRONMENT=development.vagrant.leon
fi

# Project base path
cw_path="`dirname $0`/.."
cw_path="`readlink -f ${cw_path}`"

# Nodejs libraries:
export PATH="${cw_path}/libs/node_modules/vows/bin:$PATH"
# - less-compile devtool
export NODE_PATH="${cw_path}/dev/less-compile/node_modules/less/lib:$NODE_PATH"
# - clientside test foundation
export NODE_PATH=${cw_path}/libs/node_modules/vows/lib:$NODE_PATH
export NODE_PATH=${cw_path}/libs/node_modules/zombie/lib:$NODE_PATH