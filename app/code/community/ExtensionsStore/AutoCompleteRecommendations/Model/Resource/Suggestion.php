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
	    
	    if (!$query->getId()){
	        
	        $solrResponse = $this->getSolrResponse($query);
	         
	        $response = $solrResponse->response;
	        $numFound = $response->numFound;
	         
	        if ($numFound > 0){
	             
	            try {
	                 
	                $updatedAt = date('Y-m-d H:i:s');
	                
	                $storeId = Mage::app()->getStore()->getId();
	                $query->setStoreId($storeId);
	                	                 
	                $query->setNumResults($numFound)
	                ->setPopularity(1)
	                ->setUpdatedAt($updatedAt)
	                ->save();
	                	
	            } catch(Exception $e){
	                 
	                Mage::log($e->getMessage(), null, 'extensions_store_autocompleterecommendations.log');
	            }
	             
	        } else {
	            
	        }
	        	        
	    }
	
	    return $suggestCollection;
	}	
	
}

