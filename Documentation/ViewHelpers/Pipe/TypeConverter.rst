.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-pipe-typeconverter:

==================
pipe.typeConverter
==================


Type Converter Outlet Pipe ViewHelper

Adds a TypeConverterPipe to the Form's Outlet.

Arguments
=========


.. _pipe.typeconverter_direction:

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

.. _pipe.typeconverter_targettype:

targetType
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target type (class name, integer, array, etc.)

.. _pipe.typeconverter_typeconverter:

typeConverter
-------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Class or instance of type converter. Can be a short name of a system type converter, minus "Converter" suffix, e.g. PersistentObject, Array etc.

.. _pipe.typeconverter_property:

property
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional property which needs to be converted in data. If empty, uses entire form data array as input.
