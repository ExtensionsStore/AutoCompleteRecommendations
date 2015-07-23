<?php

/**
 * Standard helper
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    
    public function getConfigDefault($defaultNodeText)
    {
        $configFile = Mage::getConfig()->getModuleDir('etc', 'Aydus_AutoCompleteRecommendations').DS.'config.xml';
        $string = file_get_contents($configFile);
        $xml = simplexml_load_string($string, 'Varien_Simplexml_Element');
        
        $path = 'default/catalog/aydus_autocompleterecommendations/'.$defaultNodeText;
        
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
    
        if (!$query->getId() || !$query->getNumResults()){
    
            $suggestCollection = $query->getSuggestCollection();
    
            if ($suggestCollection->getSize()>0){
                $query = $suggestCollection->getFirstItem();
            }
        }
    
        return $query;
    }    
    
    
}