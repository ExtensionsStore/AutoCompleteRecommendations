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
     * Generate cached recommendations for each query
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
            
            if ($storeId){
                
                if (Mage::getStoreConfig('catalog/autocompleterecommendations/cron', $storeId)){
                    $queries = Mage::getModel('catalogsearch/query')->getCollection();
                    $queries->addFieldToFilter('store_id', $storeId);
                    
                    $numGenerated = 0;
                    
                    foreach ($queries as $query){
                    
                        if ($query->getNumResults()>0 && $model->getProductRecommendationsHtml($query)){
                    
                            $numGenerated++;
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