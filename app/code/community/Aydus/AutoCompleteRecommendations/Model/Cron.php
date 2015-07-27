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
        Mage::getDesign()->setArea('frontend');//recommendations to be cached are frontend
        
        $queries = Mage::getModel('catalogsearch/query')->getCollection();
        $select = (string)$queries->getSelect();
        $model = Mage::getSingleton('aydus_autocompleterecommendations/recommendation');
        
        $numGenerated = 0;
        
        foreach ($queries as $query){
            
            if ($query->getNumResults()>0 && $model->getProductRecommendationsHtml($query)){
                
                $numGenerated++;
            }
        }
        
        $message = Mage::helper('autocompleterecommendations')->__('Recommendations generated for number of queries:');
        
        return $message . ' ' . $numGenerated;
    }
    
}