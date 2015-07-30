<?php

/**
 * Solr search engine
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Resource_Solrengine extends Aydus_AutoCompleteRecommendations_Model_Resource_Solr
{	
	/**
	 * 
	 *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
	 */
	public function getResultCollection()
	{
	    $resultCollection = Mage::getResourceModel('aydus_autocompleterecommendations/solrengine_collection');
	    
	    $query = Mage::helper('autocompleterecommendations')->getQuery();
	     
	    $solrResponse = $this->getSolrResponse($query);
	    
	    $response = $solrResponse->response;
	    $numFound = $response->numFound;
	    
	    if ($numFound > 0){
	        
	        $docs = $response->docs;
	        
	        if (count($docs)>0){
	            
	            $productIds = array();
	            
	            foreach ($docs as $doc){
	                
	                $productIds[] = $doc->id;
	                 
	            }
	            
	            if (is_array($productIds) && count($productIds)>0){
	                
	                $resultCollection->addAttributeToFilter('entity_id', array('in'=>$productIds));
	            }
	             
	        }
	        
	    }
	    	
	    return $resultCollection;
	}	
	
}

