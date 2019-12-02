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
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
	tx_fed_page_flexform text,
	tx_fed_page_flexform_sub text
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  tx_flux_migrated_version varchar(11) DEFAULT NULL,
  colPos bigint(20) DEFAULT '0' NOT NULL
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
  extension_identity varchar(128) DEFAULT 'FluidTYPO3.Builder' NOT NULL,
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
  KEY extension_identity (extension_identity)
);
