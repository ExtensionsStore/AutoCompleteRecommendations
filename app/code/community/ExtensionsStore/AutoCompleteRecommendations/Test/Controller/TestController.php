<?php 

/**
 * Controller test
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Test_Controller_TestController extends EcomDev_PHPUnit_Test_Case_Controller
{	

    /**
     * 
     * @test 
     * @loadFixture testController.yaml
     */
    public function testSuggest()
    {
        echo "\nExtensionsStore_AutoCompleteRecommendations controller test started..";
        
        $this->dispatch('catalogsearch/ajax/suggest', array('_query' => array('q' =>'test')));
        $this->assertRequestRoute('catalogsearch/ajax/suggest');
        
        $productRecommendationsHeaderText = Mage::helper('autocompleterecommendations')->getConfigDefault('label_product_recommendations');
        
        $this->assertResponseBodyContains($productRecommendationsHeaderText);        
        
    }
    
    /**
     *
     * @test
     */    
    public function testService()
    {
        echo "\nExtensionsStore_AutoCompleteRecommendations controller test completed";
        
    }
	
}