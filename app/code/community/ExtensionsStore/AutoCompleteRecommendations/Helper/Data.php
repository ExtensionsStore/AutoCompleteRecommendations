<?php

/**
 * Standard helper
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    
    public function getConfigDefault($defaultNodeText)
    {
        $configFile = Mage::getConfig()->getModuleDir('etc', 'ExtensionsStore_AutoCompleteRecommendations').DS.'config.xml';
        $string = file_get_contents($configFile);
        $xml = simplexml_load_string($string, 'Varien_Simplexml_Element');
        
        $path = 'default/catalog/autocompleterecommendations/'.$defaultNodeText;
        
        $configDefault = (string)$xml->descend($path);
        
        return $configDefault;
    }
    
    /**
     * Get search query
     * 
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        
        return $query;
    }    
    
    /**
     * Retrieve suggest collection for query
     *
     * @return Mage_CatalogSearch_Model_Resource_Query_Collection
     */
    public function getSuggestCollection($query)
    {
        if (!$query){
            $query = $this->getQuery();
        }
        
        return Mage::getModel('autocompleterecommendations/suggestion')->getSuggestCollection($query);
    }  

    /**
     * Get search engine
     * 
     * @return Enterprise_Search_Model_Resource_Engine||Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function getEngine()
    {
        $storeId = Mage::app()->getStore()->getId();
        
        if (Mage::getStoreConfig('catalog/search/engine', $storeId) == 'enterprise_search/engine'){
        
            $engine = Mage::helper('catalogsearch')->getEngine();
        
        } else if (Mage::getStoreConfig('catalog/autocompleterecommendations/solr', $storeId)) {
        
            $engine = Mage::getResourceSingleton('autocompleterecommendations/solrengine');
        
        } else {
            
            $engine = Mage::helper('catalogsearch')->getEngine();
        }
        
        return $engine;
    }
    
    
}