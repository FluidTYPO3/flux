..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/DataViewHelper.php

:edit-on-github-link: Form/DataViewHelper.php
:navigation-title: form.data
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-data:

=======================================
Form.data ViewHelper `<flux:form.data>`
=======================================

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

.. _fluidtypo3-flux-form-data_source:

Source code
===========

Go to the source code of this ViewHelper: `DataViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/DataViewHelper.php>`__.

.. _fluidtypo3-flux-form-data_arguments:

Arguments
=========

The following arguments are available for `<flux:form.data>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-data-table_argument:

table
-----

..  confval:: table
    :name: fluidtypo3-flux-form-data-table
    :type: string
    :required: true

    Name of table that contains record with Flux field

.. _fluidtypo3-flux-form-data-field_argument:

field
-----

..  confval:: field
    :name: fluidtypo3-flux-form-data-field
    :type: string
    :required: true

    Name of Flux field in table

.. _fluidtypo3-flux-form-data-uid_argument:

uid
---

..  confval:: uid
    :name: fluidtypo3-flux-form-data-uid
    :type: integer
    :required: false

    UID of record to load (used if "record" attribute not used)

.. _fluidtypo3-flux-form-data-record_argument:

record
------

..  confval:: record
    :name: fluidtypo3-flux-form-data-record
    :type: mixed
    :required: false

    Record containing Flux field (used if "uid" attribute not used)

.. _fluidtypo3-flux-form-data-as_argument:

as
--

..  confval:: as
    :name: fluidtypo3-flux-form-data-as
    :type: string
    :required: false

    Optional name of variable to assign in tag content rendering
