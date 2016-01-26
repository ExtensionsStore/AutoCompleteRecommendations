<?php


/**
 * Recommendation base source
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_System_Config_Source_Recommendationbase extends Varien_Object
{
    
    public function toOptionArray($multiselect=false)
    {
        $options = array();
        
        $recommendationBase = Mage::getModel('autocompleterecommendations/recommendation')->getRecommendationBase();
        
        foreach ($recommendationBase as $value=>$config){
            $options[] = array('value' => $value, 'label' => $config['label']);
        }
        
        return $options;
    }
    
}