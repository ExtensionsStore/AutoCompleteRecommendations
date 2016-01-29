<?php

/**
 * Suggestion model
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_Suggestion extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();
    
        $this->_init('autocompleterecommendations/suggestion');
    }
    
    /**
     * 
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Resource_Query_Collection
     */
    public function getSuggestCollection($query)
    {
    	$helper = Mage::helper('autocompleterecommendations');
    	
        if (!$query){
            
            $query = $helper->getQuery();
        }
        
        $suggestCollection = $this->getResource()->getSuggestCollection($query);
        $storeId = Mage::app()->getStore()->getId();
        $suggestionsLimit = Mage::getStoreConfig('catalog/autocompleterecommendations/suggestions_limit', $storeId);
        
        if (!$suggestionsLimit){
        	$suggestionsLimit = $helper->getConfigDefault('suggestions_limit');
        }
        
        if ($suggestionsLimit){
        	$suggestCollection->setPageSize($suggestionsLimit);
        }
        
        return $suggestCollection;
    }
    
    
}