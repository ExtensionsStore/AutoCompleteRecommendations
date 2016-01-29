<?php

/**
 * Recommendation model
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_AutoCompleteRecommendations_Model_Recommendation extends Mage_Core_Model_Abstract {
	/**
	 * Initialize resource model
	 */
	protected function _construct() {
		parent::_construct ();
		
		$this->_init ( 'autocompleterecommendations/recommendation' );
	}
	protected $_storeId;
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
	
	public function getRecommendationBase() {
		return $this->_recommendationBase;
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
    
        if ($query->getQueryText()){
    
            $storeId = $query->getStoreId();
            $cacheKey = $storeId.$query->getQueryText().$query->getNumResults();
            $cache = Mage::app()->getCache();
            $productRecommendationsHtml = $cache->load($cacheKey);
            $productRecommendationsHtml = unserialize($productRecommendationsHtml);
    
            if (!$productRecommendationsHtml){
    
                $productRecommendations = $this->_getRecommendations($query);
    
                if ($productRecommendations && $productRecommendations->getSize()>0){
    
                    $layout = Mage::getSingleton('core/layout');
                    $productRecommendationsBlock = $layout->createBlock('autocompleterecommendations/recommendation');
    
                    $productRecommendationsBlock->setType('product');
                    $productRecommendationsBlock->setRecommendations($productRecommendations);
    
                    $productRecommendationsHtml = $productRecommendationsBlock->toHtml();
    
                    $cache->save(serialize($productRecommendationsHtml), $cacheKey, array('BLOCK_HTML'), 604800);
    
                }
    
            }
    
        }
    
        return $productRecommendationsHtml;
    }
    
	public function hasRecommendations($query) {
		$productRecommendations = $this->_getRecommendations ( $query );
		
		if ($productRecommendations && $productRecommendations->getSize () > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get dom fragment to append to suggestions ul
	 *
	 * @param string $productRecommendationsHtml        	
	 * @return DOMDocumentFragment
	 */
	public function getRecommendationsDom($productRecommendationsHtml) {
		$layout = Mage::getSingleton ( 'core/layout' );
		$productRecommendationsBlock = $layout->createBlock ( 'autocompleterecommendations/recommendation' );
		
		$recommendationsDom = $productRecommendationsBlock->getRecommendationsDom ( $productRecommendationsHtml );
		
		return $recommendationsDom;
	}
	
	/**
	 *
	 * @param Mage_CatalogSearch_Model_Query $query        	
	 * @return $collection|bool
	 */
	protected function _getRecommendations($query = null) {
		$storeId = $query->getStoreId ();
		
		if (! $query) {
			
			$query = Mage::helper ( 'autocompleterecommendations' )->getQuery ();
		}
		
		$queryText = $query->getQueryText ();
		
		if ($queryText) {
			
			$productIds = ($query->getId ()) ? $this->_getSelectedRecommendations ( $query ) : array ();
			
			$collection = Mage::getResourceModel ( 'catalog/product_collection' );
			
			if (! $productIds || count ( $productIds ) == 0) {
				
				$collection = $this->_joinSearchResults ( $collection, $query );
				
				$productIds = ( array ) $this->_getBaseRecommendations ( $query );
			}
			
			if (is_array ( $productIds ) && count ( $productIds ) > 0) {
				
				$collection->addAttributeToFilter ( 'entity_id', array (
						'in',
						$productIds 
				) );
				
				if ($collection->getSize () > 0) {
					
					$collection->addAttributeToSelect ( '*' );
					$collection->addUrlRewrite ();
					$collection->addFinalPrice ();
					
					$limit = ( int ) Mage::helper ( 'autocompleterecommendations' )->getConfigDefault ( 'max_product_recommendations' );
					
					$cases = array (
							'CASE e.entity_id' 
					);
					
					foreach ( $productIds as $i => $productId ) {
						$cases [] = 'WHEN ' . $productId . ' THEN ' . $i;
					}
					
					$cases [] = 'END';
					
					$casesStr = implode ( ' ', $cases );
					
					$collection->getSelect ()->order ( new Zend_Db_Expr ( $casesStr ) );
					$collection->setPageSize ( $limit );
										
					return $collection;
				}
			}
		}
		
		return false;
	}
	protected function _joinSearchResults($collection, $query) {
		$entityIds = array ();
		$engine = Mage::helper ( 'autocompleterecommendations' )->getEngine ();
		
		//@todo doesn't work with mysql
		$resultCollection = $engine->getResultCollection ();
		$resultCollection->addSearchFilter ( $query->getQueryText () );
		
		if ($resultCollection->getSize () > 0) {
			
			$entityIds = $resultCollection->load ()->getAllIds ();
		}
		
		if (is_array ( $entityIds ) && count ( $entityIds ) > 0) {
			
			$collection->addAttributeToFilter ( 'entity_id', array (
					'in' => $entityIds 
			) );
		}
		
		return $collection;
	}
	
	/**
	 *
	 * @param Mage_CatalogSearch_Model_Query $query        	
	 * @return array
	 */
	protected function _getSelectedRecommendations($query) {
		$productIds = array ();
		
		$collection = $this->getCollection ();
		$collection->addFieldToFilter ( 'query_id', $query->getId () );
		
		if ($collection->getSize ()) {
			
			$productIds = $collection->getColumnValues ( 'product_id' );
		}
		
		return $productIds;
	}
	
	/**
	 *
	 * @param Mage_CatalogSearch_Model_Query $query        	
	 * @return array
	 */
	protected function _getBaseRecommendations($query) {
		$productIds = array ();
		
		$storeId = ( int ) $query->getStoreId ();
		$recommendationBase = Mage::getStoreConfig ( 'catalog/autocompleterecommendations/recommendation_base', $storeId );
		$resourceModel = @$this->_recommendationBase [$recommendationBase] ['resource_model'];
		$order = ( array ) @$this->_recommendationBase [$recommendationBase] ['order'];
		
		$collection = Mage::getResourceModel ( $resourceModel );
		$collection->addOrderedQty ()->setStoreId ( $storeId )->setStoreIds ( array (
				$storeId 
		) )->addStoreFilter ( $storeId )->addViewsCount ();
		
		$from = Mage::getStoreConfig ( 'catalog/autocompleterecommendations/recommendation_from', $storeId );
		if (! $from) {
			$from = date ( 'Y-01-01' );
		}
		
		$to = Mage::getStoreConfig ( 'catalog/autocompleterecommendations/recommendation_to', $storeId );
		if (! $to) {
			$to = date ( 'Y-m-d' );
		}
		
		$collection->setDateRange ( $from, $to );
		
		if ($collection->getSize ()) {
						
			$productIds = $collection->getColumnValues ( 'entity_id' );
		}
		
		return ( array ) $productIds;
	}
}