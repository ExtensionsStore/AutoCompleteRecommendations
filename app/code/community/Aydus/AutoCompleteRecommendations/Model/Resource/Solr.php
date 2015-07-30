<?php

/**
 * Suggestion resource model
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Resource_Solr extends Apache_Solr_Service
{
	/**
	 * Construct service
	 */
	public function __construct()
	{
	    $storeId = Mage::app()->getStore()->getId();
	    
	    if (Mage::getStoreConfig('catalog/search/engine', $storeId) == 'enterprise_search/engine'){
	        
	        $host = Mage::getStoreConfig('catalog/search/solr_server_hostname', $storeId);
	        $port = Mage::getStoreConfig('catalog/search/solr_server_port', $storeId);
	        $path = Mage::getStoreConfig('catalog/search/solr_server_path', $storeId);
	         
	    } else {
	        
	        $host = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/solr_server_hostname', $storeId);
	        $port = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/solr_server_port', $storeId);
	        $path = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/solr_server_path', $storeId);
	        
	        if (!$host || !$port || !$path){
	            
	            $message = Mage::helper('aydus_autocompleterecommendations')->__('Solr engine parameters, host, port and path are required.');
	            Mage::log($message, null, 'aydus_autocompleterecommendations.log');
	            Mage::throwException($message);
	            die();
	        }
	    }
	    
	    parent::__construct($host, $port, $path);
	}
	
	/**
	 * Get solr response
	 * 
	 * @param Mage_CatalogSearch_Model_Query $query
	 * @return Apache_Solr_Response
	 */
	public function getSolrResponse($query)
	{
	    if (!$query){
	    
	        $query = Mage::helper('autocompleterecommendations')->getQuery();
	    }
	    
	    $queryText = $query->getQueryText();
	    $storeId = Mage::app()->getStore()->getId();
	    
	    $queryParams = array(
	            'store_id:'.$storeId,
	            'in_stock:true',
	            '(visibility:3 OR visibility:4)',
	    );
	    
	    $locale = explode('_',Mage::app()->getLocale()->getLocaleCode());
	    $lang = $locale[0];
	    $params['qt'] = 'magento_'.$lang;
	    $params['fq'] = implode(' AND ', $queryParams);
	    $solrResponse = $this->search($queryText, 0, 10, $params);
	    
	    return $solrResponse;
	}
	
}

