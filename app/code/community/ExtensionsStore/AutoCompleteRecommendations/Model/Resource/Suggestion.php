<?php

/**
 * Suggestion resource model
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_Resource_Suggestion extends ExtensionsStore_AutoCompleteRecommendations_Model_Resource_Solr
{	
	/**
	 * 
	 *
	 * @param Mage_CatalogSearch_Model_Query $query
	 * @return Mage_CatalogSearch_Model_Resource_Query_Collection
	 */
	public function getSuggestCollection($query)
	{
	    if (!$query){
	        $query = Mage::helper('autocompleterecommendations')->getQuery();
	    }
	    
	    $suggestCollection = Mage::getResourceModel('catalogsearch/query_collection');
	    $suggestCollection->setQueryFilter($query->getQueryText());
	    $storeId = Mage::app()->getStore()->getId();
        $solr = Mage::getStoreConfig('catalog/autocompleterecommendations/solr', $storeId);
        $solrSuggestions = Mage::getStoreConfig('catalog/autocompleterecommendations/solr_suggestions', $storeId);
        
	    if ($solr && $solrSuggestions){
	    		   
	    	//@todo

	    } 
	
	    return $suggestCollection;
	}	
	
}

