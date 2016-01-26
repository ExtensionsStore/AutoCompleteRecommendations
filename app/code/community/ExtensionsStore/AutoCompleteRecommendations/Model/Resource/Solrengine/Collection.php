<?php

/**
 * Solr engine result collection
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_AutoCompleteRecommendations
 * @author     Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_AutoCompleteRecommendations_Model_Resource_Solrengine_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Add search query filter
     *
     * @param string $queryText
     * @return ExtensionsStore_AutoCompleteRecommendations_Model_Resource_Solrengine_Collection
     */
    public function addSearchFilter($queryText)
    {
        return $this;
    }

}
