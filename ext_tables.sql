#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  colPos int(11) DEFAULT '0',
  tx_flux_column varchar(255) DEFAULT NULL,
  tx_flux_parent int(11) DEFAULT NULL,
  tx_flux_children int(11) DEFAULT NULL,

  KEY index_fluxcolumn (tx_flux_column(12)),
  KEY index_fluxparentcolumn (tx_flux_column(12),tx_flux_parent)
);
