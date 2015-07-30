<?php

/**
 * Suggestion model
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Suggestion extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();
    
        $this->_init('aydus_autocompleterecommendations/suggestion');
    }
    
    /**
     * 
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Resource_Query_Collection
     */
    public function getSuggestCollection($query)
    {
        if (!$query){
            
            $query = Mage::helper('autocompleterecommendations')->getQuery();
        }
        
        $suggestCollection = $this->getResource()->getSuggestCollection($query);
        
        return $suggestCollection;
    }
    
    
}