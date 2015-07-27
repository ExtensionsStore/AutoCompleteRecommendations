<?php

/**
 * Recommendation resource model
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Resource_Recommendation extends Mage_Core_Model_Resource_Db_Abstract
{
	
	protected function _construct()
	{
		$this->_init('aydus_autocompleterecommendations/recommendation', 'recommendation_id');
	}
	
}

