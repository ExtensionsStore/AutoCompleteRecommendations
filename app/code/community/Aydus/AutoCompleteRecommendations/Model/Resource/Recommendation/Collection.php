<?php

/**
 * Recommendation resource collection model
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */
	
class Aydus_AutoCompleteRecommendations_Model_Resource_Recommendation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract 
{

	protected function _construct()
	{
        parent::_construct();
		$this->_init('aydus_autocompleterecommendations/recommendation');
	}
	
}