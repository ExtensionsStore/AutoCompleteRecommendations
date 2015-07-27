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
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();
    
        $this->_init('aydus_autocompleterecommendations/recommendation');
    }
    
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
     * @param Mage_CatalogSearch_Model_Query $query
     * @return $collection|bool
     */
    public function getRecommendations($query = null)
    {
        $storeId = Mage::app()->getStore()->getId();
        
        if (!$query){
            
            $query = Mage::helper('autocompleterecommendations')->getQuery();
        }
        
        if ($query->getId()){
            
            $productIds = (array)$this->_getSelectedRecommendations($query);
            
            $collection = Mage::getResourceModel('catalog/product_collection');

            if (!$productIds || count($productIds)==0){
                
                if (Mage::getStoreConfig('catalog/search/engine', $storeId) == 'enterprise_search/engine'){
                
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
                $selectStr = (string)$collection->getSelect();
                
                $productIds = (array)$this->_getBaseRecommendations();
                
            } 
            
            $collection->addAttributeToFilter('entity_id', array('in', $productIds));
            $selectStr = (string)$collection->getSelect();
            
            if ($collection->getSize()>0){
                    
                $collection->addAttributeToSelect('*');
                $collection->addUrlRewrite();
                $collection->addFinalPrice();
                
                $limit = (int)Mage::helper('autocompleterecommendations')->getConfigDefault('max_product_recommendations');
                            
            
                $cases = array('CASE e.entity_id');
                
                foreach($productIds as $i => $productId) {
                    $cases[] = 'WHEN '.$productId.' THEN '.$i;
                }
                
                $cases[] = 'END';
                
                $casesStr = implode(' ', $cases);
            
                $collection->getSelect()->order(new Zend_Db_Expr($casesStr));
                $collection->setPageSize($limit);
                $select = (string)$collection->getSelect();
                                            
                return $collection;
                
            }
            
        }
        
        return false;
            
    }
    
    /**
     * Get recommendations for query
     * 
     * @param Mage_CatalogSearch_Model_Query $query
     * @return string|boolean
     */
    public function getProductRecommendationsHtml($query = null)
    {
        if (!$query){
            $query = Mage::helper('autocompleterecommendations')->getQuery();
        }
        
        $productRecommendationsHtml = false;
        
        if ($query->getId() && $query->getNumResults() > 0){
            
            $storeId = Mage::app()->getStore()->getId();
            $cacheKey = $storeId.$query->getId().$query->getQueryText().$query->getNumResults();
            $cache = Mage::app()->getCache();
            $productRecommendationsHtml = $cache->load($cacheKey);
            $productRecommendationsHtml = unserialize($productRecommendationsHtml);
            
            if (!$productRecommendationsHtml){
            
                $productRecommendations = $this->getRecommendations($query);
            
                if ($productRecommendations && $productRecommendations->getSize()>0){
            
                    $layout = Mage::getSingleton('core/layout');
                    $productRecommendationsBlock = $layout->createBlock('aydus_autocompleterecommendations/recommendation');
            
                    $productRecommendationsBlock->setType('product');
                    $productRecommendationsBlock->setRecommendations($productRecommendations);
            
                    $productRecommendationsHtml = $productRecommendationsBlock->toHtml();
            
                    $cache->save(serialize($productRecommendationsHtml), $cacheKey, array('BLOCK_HTML'), 604800);
            
                }
            
            }
            
        }

        return $productRecommendationsHtml;
    }
    
    /**
     * Get dom fragment to append to suggestions ul
     * 
     * @param string $productRecommendationsHtml
     * @return DOMDocumentFragment
     */
    public function getRecommendationsDom($productRecommendationsHtml)
    {
        $layout = Mage::getSingleton('core/layout');
        $productRecommendationsBlock = $layout->createBlock('aydus_autocompleterecommendations/recommendation');
        
        $recommendationsDom = $productRecommendationsBlock->getRecommendationsDom($productRecommendationsHtml);
        
        return $recommendationsDom;
    }
    
    /**
     * 
     * @param Mage_CatalogSearch_Model_Query $query
     * @return array
     */
    protected function _getSelectedRecommendations($query)
    {
        $productIds = array();
        
        $collection = $this->getCollection();
        $collection->addFieldToFilter('query_id', $query->getId());
        
        if ($collection->getSize()) {
        
            $productIds = $collection->getColumnValues('product_id');
        }        
        
        return $productIds;
    }
    
    /**
     * 
     * @param Mage_CatalogSearch_Model_Query $query
     * @return array
     */
    protected function _getBaseRecommendations()
    {
        $productIds = array();
        
        $storeId = Mage::app()->getStore()->getId();
        $recommendationBase = Mage::getStoreConfig('catalog/aydus_autocompleterecommendations/recommendation_base', $storeId);
        $resourceModel = @$this->_recommendationBase[$recommendationBase]['resource_model'];
        $order = (array)@$this->_recommendationBase[$recommendationBase]['order'];
        
        $cacheKey = $storeId.$recommendationBase.$resourceModel;
        $cache = Mage::app()->getCache();
        $_productIds = $cache->load($cacheKey);
        $_productIds = unserialize($_productIds);
        
        if (is_array($_productIds) && count($_productIds)){
            
            $productIds = $_productIds;
            
        }else {
            
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
                $cache->save(serialize($productIds), $cacheKey, array('COLLECTION'), 86400);
            }
            
        } 
        
        return (array)$productIds;
        
    }
    

    
}