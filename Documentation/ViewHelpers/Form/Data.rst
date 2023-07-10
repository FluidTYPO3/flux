.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-data:

=========
form.data
=========


Converts raw flexform xml into an associative array, and applies any
transformation that may be configured for fields/objects.

Example: Fetch page configuration inside content element
========================================================

Since the `page` variable is available in fluidcontent elements, we
can use it to access page configuration data:

    <flux:form.data table="pages" field="tx_fed_page_flexform" record="{page}" />

Example: Check if page is accessible before loading data
========================================================

Data of disabled and deleted pages cannot be loaded with flux:form.data
and lead to an TYPO3FluidFluidCoreViewHelperException.
To prevent this exception, check if the page is accessible by generating
a link to it:

    <f:if condition="{f:uri.page(pageUid: myUid)}">
        <flux:form.data table="pages" field="tx_fed_page_flexform" uid="{myUid}" as="pageSettings">
            ...
        </flux:form.data>
    </f:if>

Arguments
=========


.. _form.data_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of table that contains record with Flux field

.. _form.data_field:

field
-----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of Flux field in table

.. _form.data_uid:

uid
---

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   UID of record to load (used if "record" attribute not used)

.. _form.data_record:

record
------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Record containing Flux field (used if "uid" attribute not used)

.. _form.data_as:

as
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of variable to assign in tag content rendering
