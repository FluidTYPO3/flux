.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-pipe-flashmessage:

=================
pipe.flashMessage
=================


FlashMessage Outlet Pipe ViewHelper

Adds a FlashMessagePipe to the Form's Outlet

Arguments
=========


.. _pipe.flashmessage_direction:

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

.. _pipe.flashmessage_message:

message
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   FlashMessage message body

.. _pipe.flashmessage_title:

title
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'Message'

:aspect:`Required`
   false
:aspect:`Description`
   FlashMessage title to use

.. _pipe.flashmessage_severity:

severity
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Severity level, as integer

.. _pipe.flashmessage_storeinsession:

storeInSession
--------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   Store message in sesssion. If FALSE, message only lives in POST. Default TRUE.
