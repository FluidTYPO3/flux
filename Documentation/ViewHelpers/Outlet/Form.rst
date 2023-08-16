.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-outlet-form:

===========
outlet.form
===========


Outlet Form Renderer

Specialised version of `f:form` which adds three vital behaviors:

- Automatic resolving of the correct extension name and plugin name
- Automatic use of "outletAction" on controller
- Addition of table name and UID as to prevent calling "outletAction"
  on any other instance than the one which rendered the form.

Together these specialised behaviors mean that the form data will
only be processed by the exact instance from which the form was
rendered, and will always target the correct plugin namespace for
the arguments to be recognised.

To customise handling of this form, add an "outletAction" to your
Flux controller with which your template is associated, e.g.
your "ContentController", "PageController" etc.

Arguments
=========


.. _outlet.form_additionalattributes:

additionalAttributes
--------------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional tag attributes. They will be added directly to the resulting HTML tag.

.. _outlet.form_data:

data
----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional data-* attributes. They will each be added with a "data-" prefix.

.. _outlet.form_aria:

aria
----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional aria-* attributes. They will each be added with a "aria-" prefix.

.. _outlet.form_action:

action
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target action

.. _outlet.form_arguments:

arguments
---------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Arguments

.. _outlet.form_controller:

controller
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target controller

.. _outlet.form_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target Extension Name (without `tx_` prefix and no underscores). If NULL the current extension name is used

.. _outlet.form_pluginname:

pluginName
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target plugin. If empty, the current plugin name is used

.. _outlet.form_pageuid:

pageUid
-------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Target page uid

.. _outlet.form_object:

object
------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Object to use for the form. Use in conjunction with the "property" attribute on the sub tags

.. _outlet.form_pagetype:

pageType
--------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Target page type

.. _outlet.form_nocache:

noCache
-------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Set this to disable caching for the target page. You should not need this.

.. _outlet.form_section:

section
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The anchor to be added to the action URI (only active if $actionUri is not set)

.. _outlet.form_format:

format
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The requested format (e.g. ".html") of the target page (only active if $actionUri is not set)

.. _outlet.form_additionalparams:

additionalParams
----------------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Additional action URI query parameters that won't be prefixed like $arguments (overrule $arguments) (only active if $actionUri is not set)

.. _outlet.form_absolute:

absolute
--------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If set, an absolute action URI is rendered (only active if $actionUri is not set)

.. _outlet.form_addquerystring:

addQueryString
--------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If set, the current query parameters will be kept in the action URI (only active if $actionUri is not set)

.. _outlet.form_argumentstobeexcludedfromquerystring:

argumentsToBeExcludedFromQueryString
------------------------------------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Arguments to be removed from the action URI. Only active if $addQueryString = TRUE and $actionUri is not set

.. _outlet.form_addquerystringmethod:

addQueryStringMethod
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   This argument is not evaluated anymore and will be removed in TYPO3 v12.

.. _outlet.form_fieldnameprefix:

fieldNamePrefix
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Prefix that will be added to all field names within this form. If not set the prefix will be tx_yourExtension_plugin

.. _outlet.form_actionuri:

actionUri
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Can be used to overwrite the "action" attribute of the form tag

.. _outlet.form_objectname:

objectName
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the object that is bound to this form. If this argument is not specified, the name attribute of this form is used to determine the FormObjectName

.. _outlet.form_hiddenfieldclassname:

hiddenFieldClassName
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   HiddenFieldClassName

.. _outlet.form_enctype:

enctype
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   MIME type with which the form is submitted

.. _outlet.form_method:

method
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Transfer type (GET or POST)

.. _outlet.form_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of form

.. _outlet.form_onreset:

onreset
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   JavaScript: On reset of the form

.. _outlet.form_onsubmit:

onsubmit
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   JavaScript: On submit of the form

.. _outlet.form_target:

target
------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target attribute of the form

.. _outlet.form_novalidate:

novalidate
----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Indicate that the form is not to be validated on submit.

.. _outlet.form_class:

class
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   CSS class(es) for this element

.. _outlet.form_dir:

dir
---

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Text direction for this HTML element. Allowed strings: "ltr" (left to right), "rtl" (right to left)

.. _outlet.form_id:

id
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Unique (in this file) identifier for this HTML element.

.. _outlet.form_lang:

lang
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Language for this element. Use short names specified in RFC 1766

.. _outlet.form_style:

style
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Individual CSS styles for this element

.. _outlet.form_title:

title
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Tooltip text of element

.. _outlet.form_accesskey:

accesskey
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Keyboard shortcut to access this element

.. _outlet.form_tabindex:

tabindex
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Specifies the tab order of this element

.. _outlet.form_onclick:

onclick
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   JavaScript evaluated for the onclick event
