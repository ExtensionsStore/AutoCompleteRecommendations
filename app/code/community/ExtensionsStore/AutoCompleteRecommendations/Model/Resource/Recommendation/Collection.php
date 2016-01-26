<?php

/**
 * Recommendation resource collection model
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */
	
class ExtensionsStore_AutoCompleteRecommendations_Model_Resource_Recommendation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract 
{

	protected function _construct()
	{
        parent::_construct();
		$this->_init('autocompleterecommendations/recommendation');
	}
	
}