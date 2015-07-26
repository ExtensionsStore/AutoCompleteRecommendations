<?php

/**
 * Recommendations block
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Block_Recommendation extends Mage_Core_Block_Template
{
    protected $_template = 'aydus/autocompleterecommendations/recommendations.phtml';
    
    public function getType()
    {
        if (!$this->hasData('type') || !$this->getData('type')){
            
            return 'product';
            
        } else {
            
            return $this->getData('type');
        }
    }
    
    public function getHeaderLabel()
    {
        $storeId = Mage::app()->getStore()->getId();
        $helper = Mage::helper('autocompleterecommendations');
        $type = $this->getType();
        
        $headerLabel = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/label_'.$type.'_recommendations', $storeId);
        $headerLabel = ($headerLabel) ? $headerLabel : $helper->getConfigDefault('label_'.$type.'_recommendations');
        $headerLabel = $helper->__($headerLabel);
        
        return $headerLabel;
    }
    
    public function getRecommendationsDom($html = null)
    {
        if (!$html){
            
            $html = $this->toHtml();
        }
        
        $dom = new DOMDocument('1.0', 'utf8');
        $dom->loadXml('<root/>');
        
        $fragment = $dom->createDocumentFragment();
        
        $fragment->appendXML($html);
        
        return $fragment;
    }
    
}