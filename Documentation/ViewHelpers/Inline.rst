:navigation-title: inline
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-inline:

=================================
inline ViewHelper `<flux:inline>`
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


.. _fluidtypo3-flux-inline_arguments:

Arguments
=========


.. _inline_code:

code
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Fluid code to be rendered as if it were part of the template rendering it. Can be passed as inline argument or tag content
