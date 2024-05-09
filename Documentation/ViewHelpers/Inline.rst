..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/InlineViewHelper.php

:edit-on-github-link: InlineViewHelper.php
:navigation-title: inline
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-inline:

=================================
Inline ViewHelper `<flux:inline>`
=================================

Inline Fluid rendering ViewHelper

Renders Fluid code stored in a variable, which you normally would
have to render before assigning it to the view. Instead you can
do the following (note, extremely simplified use case):

     $view->assign('variable', 'value of my variable');
     $view->assign('code', 'My variable: {variable}');

And in the template:

     {code -> flux:inline()}

Which outputs:

     My variable: value of my variable

You can use this to pass smaller and dynamic pieces of Fluid code
to templates, as an alternative to creating new partial templates.

.. _fluidtypo3-flux-inline_source:

Source code
===========

Go to the source code of this ViewHelper: `InlineViewHelper.php (GitHub) <fluidtypo3/flux/development/InlineViewHelper.php>`__.

.. _fluidtypo3-flux-inline_arguments:

Arguments
=========

The following arguments are available for `<flux:inline>`:

..  contents::
    :local:


.. _fluidtypo3-flux-inline-code_argument:

code
----

..  confval:: code
    :name: fluidtypo3-flux-inline-code
    :type: string
    :required: false

    Fluid code to be rendered as if it were part of the template rendering it. Can be passed as inline argument or tag content
