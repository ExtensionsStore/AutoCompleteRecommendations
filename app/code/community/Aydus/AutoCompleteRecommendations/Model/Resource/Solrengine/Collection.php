<?php

/**
 * Solr engine result collection
 *
 * @category   Aydus
 * @package    Aydus_AutoCompleteRecommendations
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_AutoCompleteRecommendations_Model_Resource_Solrengine_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Add search query filter
     *
     * @param string $queryText
     * @return Aydus_AutoCompleteRecommendations_Model_Resource_Solrengine_Collection
     */
    public function addSearchFilter($queryText)
    {
        return $this;
    }

}
