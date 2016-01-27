<?php

/**
 * Search term product recommendations
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Adminhtml_RecommendationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Ajax grid action
     */
    public function recommendationGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalogsearch/query');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalog')->__('This search no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_catalog_search', $model);

        $this->loadLayout();
        $grid = $this->getLayout()->getBlock('recommendation.edit.grid');
        if ($grid){
        	$grid->setGridSelectedProducts($this->getRequest()->getPost('grid_selected_products', null));
        }
        
        $this->renderLayout();
        
    }
}
