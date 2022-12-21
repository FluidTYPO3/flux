.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-suggest:

==============
wizard.suggest
==============


Field Wizard: Suggest

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.suggest_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.suggest_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.suggest_variables:

variables
---------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _wizard.suggest_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.suggest_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Table to search. If left out will use the table defined by the parent field

.. _wizard.suggest_pidlist:

pidList
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   List of storage page UIDs

.. _wizard.suggest_piddepth:

pidDepth
--------

:aspect:`DataType`
   integer

:aspect:`Default`
   99

:aspect:`Required`
   false
:aspect:`Description`
   Depth of recursive storage page UID lookups

.. _wizard.suggest_minimumcharacters:

minimumCharacters
-----------------

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Minimum number of characters that must be typed before search begins

.. _wizard.suggest_maxpathtitlelength:

maxPathTitleLength
------------------

:aspect:`DataType`
   integer

:aspect:`Default`
   15

:aspect:`Required`
   false
:aspect:`Description`
   Maximum path segment length - crops titles over this length

.. _wizard.suggest_searchwholephrase:

searchWholePhrase
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   A match requires a full word that matches the search value

.. _wizard.suggest_searchcondition:

searchCondition
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Search condition - for example, if table is pages "doktype = 1" to only allow standard pages

.. _wizard.suggest_cssclass:

cssClass
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Use this CSS class for all list items

.. _wizard.suggest_receiverclass:

receiverClass
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Class reference, target class should be derived from "t3lib_tceforms_suggest_defaultreceiver"

.. _wizard.suggest_renderfunc:

renderFunc
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Reference to function which processes all records displayed in results
