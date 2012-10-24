Basic usage
===========

.. warning::

   If you intend on using the phar distributed version, please read the following
   for some common phar issues:
   http://silex.sensiolabs.org/doc/usage.html#pitfalls.

You can use the ``phpdoc`` command to generate your documentation
for you.

In this document is shown how phpDocumentor can be used to generate your
documentation. It is expected that you have installed phpDocumentor using
PEAR; thus whenever we ask you to run a command it would be in the
following form::

    $ phpdoc

When you have installed a version directly from Github you should
invoke the ``phpdoc.php`` script in the ``bin`` folder of your
phpDocumentor installation unless you have added a symlink as described in the chapter
:doc:`installation`.

Under Linux / MacOSX that would be::

    $ [PHPDOC_FOLDER]/bin/phpdoc.php

And under Windows that would be::

    $ [PHPDOC_FOLDER]\bin\phpdoc

Introduction
------------

phpDocumentor takes a two-step approach to generating documentation:


1. Parse the source files and create an intermediate structure file(called
   structure.xml) containing all meta-data.
2. Transform the intermediate structure file to a form of human readable output,
   such as HTML.

These steps can be executed at once or separate, depending upon your preference.

Generating documentation
------------------------

To generate your documentation you can invoke phpDocumentor without specifying
a task::

    $ phpdoc

When ran without parameters (as shown above) it will try to get the location of
the source code and the target folder from a configuration file (which is
discussed in the :doc:`configuration` chapter) or exit with an error. You can
use the help option (``-h`` or ``--help``) to view a list of all possible actions.

::

    $ phpdoc -h

The simplest action would be to invoke phpDocumentor to parse the given
location (``-d`` for a directory, ``-f`` for a file) and tell it to
output your documentation to the given target (``-t``) folder using
the following command::

    $ phpdoc -d [SOURCE_PATH] -t [TARGET_PATH]

Please be aware that phpDocumentor expects the target location to exist
and that it is writable. If it is not, the application will exit
and tell you so.

Tasks
-----

Usage
~~~~~

phpDocumentor has a task oriented CLI; the first argument represents the name of the
task to execute, if no name is given then phpDocumentor assumes you want to run the
``project:run`` task. This last mechanism provides backwards compatibility with
phpDocumentor based configurations.

Example::

    $ phpdoc -d . -t output

Would result in the ``project:run`` task being executed with parameter ``-d`` and
``-t``.

Another example::

    $ phpdoc run -d . -t output

would have the same effect as the previous command; if no 'namespace'
(thus _project:_) is provided then the namespace ``project`` is assumed.

Last example::

   $ phpdoc project:run -d . -t output

would have the same effect as the previous examples and is the most explicit
form.

Listing
~~~~~~~

All tasks are described below with a link to their full description. Each task
has specific arguments. It is advised to either read the detailed sub-section
or invoke ``phpdoc <task> --help``; this will provide a summary of the task
and its arguments.

=============================== =================================================
Full name                       Description
=============================== =================================================
:doc:`/tasks/project_list`      Lists all tasks that can be run by phpDocumentor.
:doc:`/tasks/project_run`       Parses and transform the given directory (-d|-f)
                                to the given location (-t).
:doc:`/tasks/project_parse`     Parses the given source code and creates an
                                :term:`Intermediate Structure File`.
:doc:`/tasks/project_transform` Transforms an existing
                                :term:`Intermediate Structure File` into the
                                specified output format.
:doc:`/tasks/plugin_generate`   Generates a skeleton plugin.
:doc:`/tasks/template_generate` Generates a skeleton template.
:doc:`/tasks/template_install`  Installs a specific template.
:doc:`/tasks/template_list`     Displays a listing of all available templates in
                                phpDocumentor.
=============================== =================================================
