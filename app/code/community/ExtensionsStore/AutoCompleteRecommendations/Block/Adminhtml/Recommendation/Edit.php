<?php

/**
 * Search term recommendations grid container
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Block_Adminhtml_Recommendation_Edit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Enable grid container
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'autocompleterecommendations';
        $this->_controller = 'adminhtml_recommendation';
        $this->_headerText = Mage::helper('autocompleterecommendations')->__('Recommended Products');
        $this->_addButtonLabel = Mage::helper('autocompleterecommendations')->__('Add New Recommendation');
        parent::__construct();
        $this->_removeButton('add');
    }

}
