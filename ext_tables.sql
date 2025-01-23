#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_fed_page_flexform text,
	tx_fed_page_flexform_sub text,
	tx_fed_page_controller_action varchar(255) DEFAULT '' NOT NULL,
	tx_fed_page_controller_action_sub varchar(255) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  tx_flux_migrated_version varchar(11) DEFAULT NULL,
  colPos bigint(20) DEFAULT '0' NOT NULL,
  t3_origuid int(11) unsigned DEFAULT '0' NOT NULL
);

#
# Table structure for table 'content_types'
#
CREATE TABLE content_types (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(30) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,
  editlock tinyint(4) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,

  title varchar(400),
  content_type varchar(128),
  description text,
  extension_identity varchar(128) DEFAULT 'FluidTYPO3.Flux' NOT NULL,
  content_configuration text,
  grid text,
  template_source text,
  template_file varchar(128),
  icon varchar(200),
  validation varchar(1),
  template_dump varchar(1),

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY content_type (content_type),
  KEY fe_enabled (deleted, hidden),
  KEY extension_identity (extension_identity)
);

#
# Table structure for table 'flux_sheet'
#
CREATE TABLE flux_sheet (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  editlock tinyint(4) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l10n_parent int(11) DEFAULT '0' NOT NULL,
  l10n_diffsource mediumtext,

  name varchar(255),
  sheet_label mediumtext,
  source_table varchar(255),
  source_field varchar(255),
  source_uid int(11) DEFAULT '0' NOT NULL,
  form_fields int(11) DEFAULT '0' NOT NULL,
  json_data text,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'flux_field'
#
CREATE TABLE flux_field (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  editlock tinyint(4) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l10n_parent int(11) DEFAULT '0' NOT NULL,
  l10n_diffsource mediumtext,

  parent_field int(11) DEFAULT '0' NOT NULL,
  sheet int(11) DEFAULT '0' NOT NULL,
  field_name varchar(255),
  field_label mediumtext,
  field_type varchar(32),
  field_value text,
  field_options text,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY parent_field (parent_field),
  KEY sheet (sheet),
  KEY field_value (field_value(32))
);
