<?php

/**
 * Cron
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Cron 
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
        $model = Mage::getSingleton('aydus_autocompleterecommendations/recommendation');
        $message = Mage::helper('autocompleterecommendations')->__('Recommendations generated for number of queries:');
        
        foreach ($stores as $store){
            
            $storeId = $store->getId();
            
            if ($storeId){
                
                if (Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/cron', $storeId)){
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