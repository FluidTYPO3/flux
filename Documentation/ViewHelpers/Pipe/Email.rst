.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-pipe-email:

==========
pipe.email
==========


Email Outlet Pipe ViewHelper

Adds an EmailPipe to the Form's Outlet

Arguments
=========


.. _pipe.email_direction:

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

.. _pipe.email_body:

body
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Message body. Can also be inserted as tag content

.. _pipe.email_bodysection:

bodySection
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Section to use for the body

.. _pipe.email_subject:

subject
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Message subject

.. _pipe.email_recipient:

recipient
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Message recipient address or name+address as "Name <add@ress>"

.. _pipe.email_sender:

sender
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Message sender address or name+address as "Name <add@ress>"
