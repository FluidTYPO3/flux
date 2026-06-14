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
