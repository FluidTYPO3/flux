#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  colPos int(11) DEFAULT '0',
  tx_flux_column varchar(255) DEFAULT NULL,
  tx_flux_parent int(11) DEFAULT NULL,
  tx_flux_children int(11) DEFAULT NULL,
  KEY flux_column (tx_flux_column),
  KEY flux_parent (tx_flux_parent),
);
