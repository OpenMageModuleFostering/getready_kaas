<?php
/**
 * Magento Module developed by Getready s.r.o
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@getready.cz so we can send you a copy immediately.
 * 
 * @copyright  Copyright (c) 2015 Getready s.r.o. (http://getready.cz)
 *
 */
/**
 * 
 * 
 *
 * @category   Getready
 * @package    Getready_Kaas
 * @author     Getready Team <info@getready.cz>
 */
class Getready_Kaas_Helper_Product_Store_Data extends Mage_Core_Helper_Abstract
{
	protected $_store_category_ids = array();
	protected $_store_category_ids_loaded = false;
	
	public function isStoreCategory($category_id, $store_id)
	{			
		$is_store_category = false;
		if(!$this->_store_category_ids_loaded)
		{
			$this->_loadStoreCategoryIds($store_id);
		}
		else
		{
			//log load not needed
		}
		
		if(in_array($category_id, $this->_store_category_ids))
		{
			$is_store_category = true;
		}
		
		return $is_store_category;
	}
	
	protected function _loadStoreCategoryIds($store_id)
	{
		$store_category_ids = array();
		$store = Mage::app()->getStore($store_id);
		if($store->getId())
		{
			$root_id = $store->getRootCategoryId();
			
			/* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
			$tree = Mage::getResourceSingleton('catalog/category_tree')->load();

			$root = $tree->getNodeById($root_id);
			
			$collection = Mage::getModel('catalog/category')->getCollection()
				->setStoreId($store_id)
				->addAttributeToSelect('name')
				->addAttributeToSelect('is_active');

			$tree->addCollectionData($collection, true);

			$store_category_ids = $this->_nodeToArray($root);
		}
		
		
		$this->_store_category_ids = $store_category_ids;
		$this->_store_category_ids_loaded = true;
	}
	
	protected function _nodeToArray(Varien_Data_Tree_Node $node)
    {
		$result = array();
		$category_id = $node->getId();
		
		$result[] = $category_id;
		
		foreach ($node->getChildren() as $child) {
            $result = array_merge($result,$this->_nodeToArray($child));
        }
		
        return $result;
    }
}