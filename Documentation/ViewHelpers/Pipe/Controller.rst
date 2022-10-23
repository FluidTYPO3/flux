.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-pipe-controller:

===============
pipe.controller
===============


Controller Action Outlet Pipe ViewHelper

Adds a ControllerPipe to the Form's Outlet.

Arguments
=========


.. _pipe.controller_direction:

direction
---------

:aspect:`DataType`
   string

:aspect:`Default`
   'out'

:aspect:`Required`
   false
:aspect:`Description`
   Which endpoint to attach the Pipe to - either "in" or "out". See documentation about Outlets and Pipes

.. _pipe.controller_action:

action
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Action to call on the controller, minus the "Action" suffix

.. _pipe.controller_controller:

controller
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Class name of controller to call. If empty, uses current controller

.. _pipe.controller_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Extension name of controller to call. If empty, uses current extension name
