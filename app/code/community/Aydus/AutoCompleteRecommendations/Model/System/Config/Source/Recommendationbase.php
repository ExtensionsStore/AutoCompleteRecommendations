<?php


/**
 * Recommendation base source
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_System_Config_Source_Recommendationbase extends Varien_Object
{
    
    public function toOptionArray($multiselect=false)
    {
        $options = array();
        
        $recommendationBase = Mage::getModel('aydus_autocompleterecommendations/recommendation')->getRecommendationBase();
        
        foreach ($recommendationBase as $value=>$config){
            $options[] = array('value' => $value, 'label' => $config['label']);
        }
        
        return $options;
    }
    
}