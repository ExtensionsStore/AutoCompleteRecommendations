<?php

/**
 * Recommendation model
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Recommendation extends Mage_Core_Model_Abstract
{
    //const RECOMMENDATION_MOST_DOWNLOADS = 'downloads';
    const RECOMMENDATION_MOST_ORDERED = 'ordered';
    const RECOMMENDATION_MOST_SOLD = 'sold';
    const RECOMMENDATION_MOST_VIEWED = 'viewed';
    
    protected $_recommendationBase = array(
            //self::RECOMMENDATION_MOST_DOWNLOADS => array('label'=> 'Most Downloaded', 'resource_model' => 'reports/product_downloads_collection', 'order'=>array('ordered_qty', 'desc')),
            self::RECOMMENDATION_MOST_ORDERED => array('label'=> 'Most Ordered', 'resource_model' => 'reports/product_ordered_collection', 'order'=>array('ordered_qty', 'desc')),
            self::RECOMMENDATION_MOST_SOLD => array('label'=> 'Most Sold', 'resource_model' => 'reports/product_sold_collection', 'order'=>array('ordered_qty', 'desc')),
            self::RECOMMENDATION_MOST_VIEWED => array('label'=> 'Most Viewed', 'resource_model' => 'reports/product_viewed_collection', 'order'=>array('views', 'desc')),
    );
    
    public function getRecommendationBase()
    {
        return $this->_recommendationBase;
    }
    
    /**
     * @param int $limit
     * @return $collection|bool
     */
    public function getRecommendations($limit = null)
    {
        $storeId = Mage::app()->getStore()->getId();
        $query = Mage::helper('autocompleterecommendations')->getQuery();
        
        if ($query->getId()){
            
            $collection = Mage::getResourceModel('catalog/product_collection');
                        
            if (Mage::getStoreConfig('catalog/search/engine', $storeId) == 'enterprise_search/engine'){
                
                $data = $query->getData();
                
                $engine = Mage::helper('catalogsearch')->getEngine();
                
                $resultCollection = $engine->getResultCollection();
                $resultCollection->addSearchFilter($query->getQueryText());
                                
                $entityIds = $resultCollection->load()->getAllIds();
                
                $collection->addAttributeToFilter('entity_id', array('in' => $entityIds));
                
            } else {
                
                $collection->getSelect()->joinInner(
                        array('search_result' => $collection->getTable('catalogsearch/result')),
                        $collection->getConnection()->quoteInto(
                                'search_result.product_id=e.entity_id AND search_result.query_id=?',
                                $query->getId()
                        ),
                        array('relevance' => 'relevance')
                );
                
            }
                        
            if ($collection->getSize()>0){
                
                //get most 
                $productIds = $this->_getProductIds();
                
                if (count($productIds) > 0){
                    
                    $collection->addAttributeToSelect('*');
                    $collection->addUrlRewrite();
                    $collection->addFinalPrice();
                    
                    $limit = ($limit) ? $limit : (int)Mage::helper('autocompleterecommendations')->getConfigDefault('max_product_recommendations');
                                
                    $collection->addAttributeToFilter('entity_id', array('in', $productIds));
                
                    $cases = array('CASE e.entity_id');
                    
                    foreach($productIds as $i => $productId) {
                        $cases[] = 'WHEN '.$productId.' THEN '.$i;
                    }
                    
                    $cases[] = 'END';
                    
                    $casesStr = implode(' ', $cases);
                
                    $collection->getSelect()->order(new Zend_Db_Expr($casesStr));
                    $collection->setPageSize($limit);
                    $select = $collection->getSelect();
                                                
                    return $collection;
                }
                
            }
            
        }
        
        return false;
            
    }
    
    
    protected function _getProductIds()
    {
        $storeId = Mage::app()->getStore()->getId();
        
        $productIds = array();
        
        $recommendationBase = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/recommendation_base', $storeId);
        
        $resourceModel = @$this->_recommendationBase[$recommendationBase]['resource_model'];
        $order = (array)@$this->_recommendationBase[$recommendationBase]['order'];
        
        $collection = Mage::getResourceModel($resourceModel);
        $collection->addOrderedQty()
        ->setStoreId($storeId)
        ->setStoreIds(array($storeId))
        ->addStoreFilter($storeId)
        ->addViewsCount();
        
        $from = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/recommendation_from', $storeId);
        if (!$from){
            $from = date('Y-01-01');
        }
        
        $to = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/recommendation_to', $storeId);
        if (!$to){
            $to = date('Y-m-d');
        }
        
        $collection->setDateRange($from, $to);
                
        $select = (string)$collection->getSelect();
        
        if ($collection->getSize()){
            
            $productIds = $collection->getColumnValues('entity_id');
        }
        
        return $productIds;
        
    }
    

    
}