<?php

/**
 * Cron
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_Cron 
{

    /**
     * Generate recommendations for each query
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function generateRecommendations($schedule)
    {
        $stores = Mage::getModel('core/store')->getCollection();
        Mage::getDesign()->setArea('frontend');
        $model = Mage::getSingleton('autocompleterecommendations/recommendation');
        $message = Mage::helper('autocompleterecommendations')->__('Recommendations generated for number of queries:');
        
        foreach ($stores as $store){
            
            $storeId = $store->getId();
            $enabled = Mage::getStoreConfig('catalog/autocompleterecommendations/enabled', $storeId);
            
            if ($enabled){
            	
            	$cron = Mage::getStoreConfig('catalog/autocompleterecommendations/cron', $storeId);
                
                if ($cron){
                    $queries = Mage::getModel('catalogsearch/query')->getCollection();
                    $queries->addFieldToFilter('store_id', $storeId);
                    $queries->addFieldToFilter('num_results', array('gt'=>10));
                    $queries->addFieldToFilter('popularity', array('gt'=>100));
                    
                    $numGenerated = 0;
                    $size = $queries->getSize();
                    
                    if ($size){
                    	foreach ($queries as $query){
                    		
                    		$queryText = $query->getQueryText();
                    	
                    		if ($model->getProductRecommendationsHtml($query)){
                    			                    	
                    			$numGenerated++;
                    		}
                    	}                    	
                    }
                    
                    $message .= ' Store '.$storeId.':' . $numGenerated.'.';    
                                    
                } else {
                    
                    $message .= ' Store '.$storeId.': disabled.';
                }

            }           
            
        }
        
        return $message;
    }
    
}