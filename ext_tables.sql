#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	attributes int(11) DEFAULT '0',
	tx_flux_column varchar(255) DEFAULT NULL,
	tx_flux_parent int(11) DEFAULT NULL,
	tx_flux_children int(11) DEFAULT NULL,

	KEY index_fluxcolumn (tx_flux_column(12)),
	KEY index_fluxparentcolumn (tx_flux_column(12),tx_flux_parent)
);

CREATE TABLE tx_flux_domain_model_attribute (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	for_table varchar(255) DEFAULT '',
	for_field varchar(255) DEFAULT '',
	for_identity varchar(255) DEFAULT '' NOT NULL,

	name varchar(255) DEFAULT '',
	sheet varchar(255) DEFAULT '',
	attribute_values int(11) DEFAULT NULL,

	sorting int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY attribute_name (name),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_flux_domain_model_value (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	attribute int(11) DEFAULT '0' NOT NULL,

	value blob,

	sorting int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_attribute (attribute),
	KEY language (l10n_parent,sys_language_uid)
);
