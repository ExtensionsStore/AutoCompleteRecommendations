<?php

/**
 * Sql setup
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('extensions_store_autocompleterecommendations_recommendation')}` (
  `recommendation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `date_recommended` datetime NOT NULL,
  PRIMARY KEY (`recommendation_id`),
  KEY `QUERY_PRODUCT` (`query_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();