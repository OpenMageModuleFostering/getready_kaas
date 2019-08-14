<?php
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('getready_kaas_activity')};
CREATE TABLE {$this->getTable('getready_kaas_activity')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default 0,
  `product_id` int(11) NOT NULL default 0,
  `action` VARCHAR(255) NULL ,    
  `created_at` DATETIME NULL ,
  PRIMARY KEY (`id`),
  INDEX `getready_activity_feed_store_index1` (`store_id`),  
  INDEX `getready_activity_feed_index1` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();