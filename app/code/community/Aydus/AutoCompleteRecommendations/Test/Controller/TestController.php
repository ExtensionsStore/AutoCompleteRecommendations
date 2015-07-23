<?php 

/**
 * Controller test
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Test_Controller_TestController extends EcomDev_PHPUnit_Test_Case_Controller
{	

    /**
     * 
     * @test 
     * @loadFixture testController.yaml
     */
    public function testSuggest()
    {
        echo "\nAydus_AutoCompleteRecommendations controller test started..";
        
        $this->dispatch('catalogsearch/ajax/suggest', array('_query' => array('q' =>'test')));
        $this->assertRequestRoute('catalogsearch/ajax/suggest');
        
        $productRecommendationsHeaderText = Mage::helper('aydus_autocompleterecommendations')->getConfigDefault('label_product_recommendations');
        
        $this->assertResponseBodyContains($productRecommendationsHeaderText);        
        
    }
    
    /**
     *
     * @test
     */    
    public function testService()
    {
        echo "\nAydus_AutoCompleteRecommendations controller test completed";
        
    }
	
}