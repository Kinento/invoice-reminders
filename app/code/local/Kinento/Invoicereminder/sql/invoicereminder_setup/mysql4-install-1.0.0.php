<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
CREATE TABLE {$this->getTable('invoicereminder')} (
  `entry_id` int(10) unsigned NOT NULL auto_increment,
  `increment_id` varchar(250) NOT NULL default '',
  `invoicereminders` int(10) NOT NULL default '0',
  `status` enum('enabled','disabled') NOT NULL default 'enabled',
  PRIMARY KEY  (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");
 
$installer->endSetup();
?>