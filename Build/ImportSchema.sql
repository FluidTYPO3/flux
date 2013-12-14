INSERT INTO be_users (pid, tstamp, username, password, admin, usergroup, disable, starttime, endtime, lang, email) VALUES (0,1276860841,'_cli_lowlevel','5f4dcc3b5aa765d61d8327deb882cf99',0,'1',0,0,0,'','_cli_phpunit@example.com');
ALTER TABLE `tt_content` ADD `tx_flux_column` varchar(255) DEFAULT NULL;
ALTER TABLE `tt_content` ADD `tx_flux_parent` int(11) DEFAULT NULL;
