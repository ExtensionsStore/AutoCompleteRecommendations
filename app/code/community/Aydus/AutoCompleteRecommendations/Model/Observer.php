<?php

/**
 * Observer
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Observer 
{
    protected $_maxProductRecommendations;
    
    /**
     * @see core_block_abstract_to_html_after
     * @param Varien_Event_Observer $observer
     */
    public function appendTopRecommendation($observer)
    {
        $storeId = Mage::app()->getStore()->getId();
        
        $query = Mage::helper('autocompleterecommendations')->getQuery();
        
        if ($query->getId() && $query->getNumResults()){
            
            $enabled = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/enabled', $storeId);
            $this->_maxProductRecommendations = (int)Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/max_product_recommendations', $storeId);
            
            if ($enabled && $this->_maxProductRecommendations > 0){
            
                $block = $observer->getBlock();
            
                if (get_class($block) == 'Mage_CatalogSearch_Block_Autocomplete') {
            
                    $transport = $observer->getTransport();
                    $html = $transport->getHtml();
            
                    if ($html){
                        $html = $this->_appendTopRecommendations($html);
                    }
            
                    $transport->setHtml($html);
            
                }
            }
            
        }
        
        return $observer;
        
    }
    
    /**
     * Append product recommendations to Autocomplete block html
     * 
     * @param string $html
     * @return string
     */
    protected function _appendTopRecommendations($html)
    {
        $layout = Mage::getSingleton('core/layout');
        
        $dom = new DOMDocument('1.0', 'utf8');
        
        $dom->loadHTML($html);
        
        $uls = $dom->getElementsByTagName('ul');
        $ul = $uls->item(0);
        
        if ($this->_maxProductRecommendations > 0){
            
            $productRecommendations = Mage::getModel('aydus_autocompleterecommendations/recommendation')->getRecommendations($this->_maxProductRecommendations);
            
            if ($productRecommendations && $productRecommendations->count()>0){
                
                $productRecommendationsBlock = $layout->createBlock('aydus_autocompleterecommendations/recommendation');
                $productRecommendationsBlock->setType('product');
                $productRecommendationsBlock->setRecommendations($productRecommendations);
                
                $productRecommendationsDom = $productRecommendationsBlock->getRecommendationsDom();
                
                $productRecommendationsDom = $dom->importNode($productRecommendationsDom, true);                
                $ul->appendChild($productRecommendationsDom);
                
            }
            
        }
                
        $dom->removeChild($dom->doctype);
        $dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);
                
        $html = $dom->saveHTML();
        
        return $html;
    }
    
    
}