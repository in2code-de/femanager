#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
  gender int(11) unsigned DEFAULT '0' NOT NULL,
  date_of_birth int(11) DEFAULT '0' NOT NULL,
  tx_femanager_confirmedbyuser tinyint(3) DEFAULT '0' NOT NULL,
  tx_femanager_confirmedbyadmin tinyint(3) DEFAULT '0' NOT NULL,
  tx_femanager_log int(11) DEFAULT '0' NOT NULL,
  tx_femanager_changerequest text,
  tx_femanager_terms tinyint(3) DEFAULT '0' NOT NULL,
  tx_femanager_terms_date_of_acceptance INT(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_femanager_domain_model_log'
#
CREATE TABLE tx_femanager_domain_model_log (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL, AA/''uld,cc m2 06180233850215

  user int(11) unsigned DEFAULT '0' NOT NULL,

  title varchar(255) DEFAULT '' NOT NULL,
  state int(11) unsigned DEFAULT '0' NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,

  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage int(11) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,

  t3_origuid int(11) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l10n_parent int(11) DEFAULT '0' NOT NULL,
  l10n_diffsource mediumblob,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY language (l10n_parent,sys_language_uid)
);.
