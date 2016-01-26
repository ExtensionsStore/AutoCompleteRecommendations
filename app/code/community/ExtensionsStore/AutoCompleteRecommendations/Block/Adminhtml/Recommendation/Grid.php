<?php

/**
 * Search term recommendations grid
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Block_Adminhtml_Recommendation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init Grid default properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('recommendation_product_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getQuery()
    {
        return Mage::registry('current_catalog_search');
    }
    
    protected function _getProductCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect(array('entity_id','name','sku','status','visibility','price'));
        $collection->addAttributeToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility',Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        
        return $collection;
    }

    /**
     * Prepare collection for Grid
     *
     * @return ExtensionsStore_AutoCompleteRecommendations_Block_Adminhtml_Recommendation_Grid
     */
    protected function _prepareCollection()
    {
        $this->setDefaultFilter(array('product_id_selected' => 1));
        $query = $this->getQuery();
        
        $collection = $this->_getProductCollection();
        $collection->joinTable(
                array('recommendation' => 'autocompleterecommendations/recommendation'),
                'product_id=entity_id',
                array('recommendation_id', 'query_id', 'position'),
                'query_id = '.$query->getId(),
                'left'
        );        
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return ExtensionsStore_AutoCompleteRecommendations_Block_Adminhtml_Recommendation_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedRecommendations();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedRecommendations(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 60,
            'editable'          => true,
            'edit_only'         => false
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/recommendation/recommendationGrid', array('_current' => true));
    }
    
    /**
     * Retrieve recommended products 
     *
     * @return array
     */
    protected function _getSelectedRecommendations()
    {
        $products = $this->getGridSelectedProducts();
        
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedRecommendations());
        }
        
        return $products;
    }

    /**
     * Retrieve recommended products
     *
     * @return array
     */
    public function getSelectedRecommendations()
    {
        $products = array();
        
        $query = $this->getQuery();
        
        $collection = $this->_getProductCollection();
                
        $collection->joinTable(
                array('recommendation' => 'autocompleterecommendations/recommendation'),
                'product_id=entity_id',
                array('recommendation_id', 'query_id', 'position'),
                'query_id = '.$query->getId(),
                'inner'
        );        
                
        foreach ($collection as $product) {
            $products[$product->getId()] = array('position' => $product->getPosition());
        }
        
        return $products;
    }
}
