<?php

/**
 * Sql setup
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXIST `{$this->getTable('aydus_autocompleterecommendations_recommendation')}` (
  `recommendation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `date_recommended` datetime NOT NULL,
  PRIMARY KEY (`recommendation_id`),
  KEY `QUERY_PRODUCT` (`query_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();