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
class Getready_Kaas_Model_Category_Api extends Mage_Api_Model_Resource_Abstract
{
    public function info($categoryId,$storeId = null)
    {				
        $category = Mage::getModel('catalog/category');
        if(!is_null($storeId))
        {
            $category->setStoreId($storeId);
        }
        $category->load($categoryId);

        if (!$category->getId()) {
            $this->_fault('category_not_exists');
        }
	
        $category_helper = Mage::Helper('kaas_category')->setStoreId($storeId);       
        $category_info = $category_helper->getCategoryInfo($category,1);

        return $category_info;
    }
	
	
	
    public function items($storeId)
    {		
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }
						
        $root_id = $store->getRootCategoryId();
		
        /* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();

        $root = $tree->getNodeById($root_id);
		
        if($root && $root->getId() == 1) {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');

        $tree->addCollectionData($collection, true);

        $category_info = $this->_nodeToArray($root,$storeId);
		
				
        return $category_info;
    }
	
    protected function _nodeToArray(Varien_Data_Tree_Node $node,$store_id = 0)
    {
        $default_root_level = Mage::Helper('kaas')->getDefaultRootLevel();
        $result = array();
        $category_id = $node->getId();
        $category = Mage::Helper('kaas_category_cache')->getCategory($category_id, $store_id);
        
        if($category && $category->getIsActive())
        {
            $category_helper = Mage::Helper('kaas_category')->setStoreId($store_id);
            //$category_helper->setStoreId($storeId);
            $category_info = $category_helper->getCategoryInfo($category,$default_root_level);

            if($category_info && isset($category_info['level']) && $category_info['level'] > 0 )
            {
                $result[] = $category_info;
            }

            foreach ($node->getChildren() as $child) {
                $result = array_merge($result,$this->_nodeToArray($child, $store_id));
            }
        }
		
        return $result;
    }
   
}