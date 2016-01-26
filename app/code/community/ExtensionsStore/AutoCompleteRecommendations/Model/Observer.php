<?php

/**
 * Observer
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_Observer 
{    
    /**
     * @see controller_action_postdispatch_catalogsearch_ajax_suggest
     * @param Varien_Event_Observer $observer
     */
    public function appendTopRecommendation($observer)
    {
        $storeId = Mage::app()->getStore()->getId();
        $enabled = Mage::getStoreConfig('catalog/autocompleterecommendations/enabled', $storeId);
        
        if ($enabled){
            
            $event = $observer->getEvent();
            $controller = $event->getControllerAction();
            $response = $controller->getResponse();
            $html = $response->getBody();
                    
            if ($html){
                $html = $this->_appendTopRecommendations($html);
            } else {
                
                if (Mage::getStoreConfig('catalog/autocompleterecommendations/solr', $storeId)){
                    
                    $layout = Mage::getSingleton('core/layout');
                    $suggestionsBlock = $layout->createBlock('autocompleterecommendations/suggestion');
                    
                    $html = $suggestionsBlock->toHtml();
                    
                    if ($html){
                        $html = $this->_appendTopRecommendations($html);
                    }
                    
                }
                
            }
        
            $response->setBody($html);
            
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
        $recommendationsModel = Mage::getModel('autocompleterecommendations/recommendation');
        $query = Mage::helper('catalogsearch')->getQuery();
        $productRecommendationsHtml = $recommendationsModel->getProductRecommendationsHtml($query);
        
        if ($productRecommendationsHtml){
            
            $dom = new DOMDocument('1.0', 'utf8');
            
            $dom->loadHTML($html);
            
            $uls = $dom->getElementsByTagName('ul');
            $ul = $uls->item(0);
            
            $productRecommendationsDom = $recommendationsModel->getRecommendationsDom($productRecommendationsHtml);
        
            $productRecommendationsDom = $dom->importNode($productRecommendationsDom, true);
            $ul->appendChild($productRecommendationsDom);
            
            $dom->removeChild($dom->doctype);
            $dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);
            
            $html = $dom->saveHTML();
        }
        
        return $html;
    }
    
    /**
     * Save admin selected recommendations
     * 
     * @see catalogsearch_query_save_after
     * @param Varien_Event_Observer $observer
     */    
    public function saveRecommendations($observer)
    {
        try {
            
            $query = $observer->getCatalogsearchQuery();
            
            $request = Mage::app()->getRequest();
            
            $selectedProducts = $request->getParam('grid_selected_products');
            
            $selectedProducts = explode('&', $selectedProducts);
            
            if (is_array($selectedProducts) && count($selectedProducts)>0){
            
                $recommendationsData = array();
            
                foreach ($selectedProducts as $selectedProduct){
            
                    $selectedProduct = explode('=', $selectedProduct);
            
                    if (is_array($selectedProduct) && count($selectedProduct)>1){
            
                        $productId = $selectedProduct[0];
                        $params = urldecode($selectedProduct[1]);
            
                        $params = @base64_decode($params, true);
            
                        $params = explode('=',$params);
            
                        if (is_array($params) && count($params)>0){
            
                            $position = (int)$params[1];
            
                            $recommendationsData[] = array(
                                    'query_id' => $query->getId(),
                                    'product_id' => $productId,
                                    'position'=>$position,
                                    'date_recommended' => date('Y-m-d H:i:s')
                            );
            
                        }
            
                    }
            
                }
            
                if (is_array($recommendationsData) && count($recommendationsData)>0){
            
                    $collection = Mage::getModel('autocompleterecommendations/recommendation')->getCollection();
                    $collection->addFieldToFilter('query_id',$query->getId());
            
                    foreach ($collection as $recommendation){
                        $recommendation->delete();
                    }
            
                    foreach ($recommendationsData as $data){
            
                        $recommendation = Mage::getModel('autocompleterecommendations/recommendation');
            
                        $recommendation->setData($data);
            
                        $recommendation->save();
                    }
            
                }
                
            }
                        
        } catch(Exception $e){
            
            Mage::log($e->getMessage, null, 'extensions_store_autocompleterecommendations.log');
        }
        
    }
    
}