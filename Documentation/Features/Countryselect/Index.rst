.. include:: ../../Includes.txt


.. _countryselect:

Using static_info_tables for country selection
----------------------------------------------

Basics
^^^^^^

- Install Extension static_info_tables
- Install Extension static_info_tables(_de)(_fr)(_pl) etc... for localized countrynames
- Import Records of the extensions via Extension Manager (see manual of static_info_tables)
- Clear Cache
- Copy all Partials from femanager to a fileadmin folder
- Set the new Partial Path via Constants: plugin.tx_femanager.view.partialRootPath = fileadmin/femanager/Partials/
- Open Partial Fields/Country.html and activate static_info_tables (see notes in HTML-File)

Details for Partial Country.html
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The idea is very simple. You can change the “options Attribute” of the form.select ViewHelper:



.. code-block:: text

	<femanager:form.select
		id="femanager_field_country"
		property="country"
		options="{femanager:Form.GetCountriesFromStaticInfoTables()}"
		defaultOption="{f:translate(key:'pleaseChoose')}"
		class="input-block-level"
		additionalAttributes="{femanager:Validation.FormValidationData(settings:'{settings}',fieldName:'country')}" />

The GetCountriesFromStaticInfoTables-ViewHelper

Possible options for this ViewHelper are:

.. t3-field-list-table::
 :header-rows: 1

 - :Name:
      Name
   :Description:
      Description
   :Default:
      Default Value
   :Examplevalue:
      Example Value

 - :Name:
      key
   :Description:
      Define the Record Column of static_countries table which should be used for storing to fe_users country

      Note: Please use lowerCamelCase Writing for Fieldnames

   :Default:
      isoCodeA3
   :Examplevalue:
      isoCodeA2

 - :Name:
      value
   :Description:
      Define the Record Column of static_countries table which should be visible in selection in femanager

      Note: Please use lowerCamelCase Writing for Fieldnames


   :Default:
      officialNameLocal
   :Examplevalue:
      shortNameFr

 - :Name:
      sortbyField
   :Description:
      Define the Record Column of static_countries which should be used for a sorting

      Note: Please use lowerCamelCase Writing for Fieldnames

   :Default:
      isoCodeA3
   :Examplevalue:
      shortNameDe

 - :Name:
      sorting
   :Description:
      Could be 'asc' or 'desc' for Ascending or Descending Sorting
   :Default:
      asc
   :Examplevalue:
      desc

Some Examples are:

.. code-block:: text

	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA2',value:'shortNameDe')}
	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA2',value:'shortNameFr',sortbyField:'shortNameFr')}
	{femanager:Form.GetCountriesFromStaticInfoTables(key:'isoCodeA3',value:'isoCodeA3',sortbyField:'isoCodeA3',sorting:'asc')}
