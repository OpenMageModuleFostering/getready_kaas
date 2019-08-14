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
class Getready_Kaas_Helper_Product_Category_Data extends Mage_Core_Helper_Abstract
{
    protected $_categories = array();
    protected $_generated_categories = array();	
    
    protected $_store_id = 0;
    
    public function setStoreId($store_id)
    {
        $this->_store_id = $store_id;
        return $this;
    }
    
    public function getStoreId()
    {
        return $this->_store_id;
    }

    public function getCategoryInfo($category_id)
    {
        $category_info = array();
        $store_id = $this->getStoreId();
        $category_key = $category_id . '_' . $store_id;
        if(isset($this->_generated_categories[$category_key]))
        {
            $category_info = $this->_generated_categories[$category_key];			
        }
        else
        {
            $category_info = $this->_generateCategoryInfo($category_id);
            $this->_generated_categories[$category_key] = $category_info;			
        }
        return $category_info;
    }
	
    public function _generateCategoryInfo($category_id)
    {
        $root_level = Mage::Helper('kaas')->getDefaultRootLevel(); // 1;
        $category_info = null;

        $category = $this->_getCategory($category_id);
        if($category->getId() && $category->getIsActive())
        {
            $category_info = array(
                'id' => '',
                'name' => '',
                'path_ids' => '',
                'level' => '',
                'parent_id' => '',
                'parent_name' => '',
                'url' => '',
                'path_url_key' => '',
                'path' => '',
                'root_id' => '',
                'root_name' => '',
            );
                        
            $category_info['id'] = $category->getId();
            $category_info['name'] = $category->getName();
                        
            $base_url = Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
            $url_path = $category->getUrlPath();
            $category_info['url'] = $base_url . $url_path; //$category->getUrl();

            $category_path_ids = $this->_formatPathIdsForRootCategory($category->getPath(),$root_level);

            $category_info['path_ids'] = $category_path_ids;
            $category_info['path_url_key'] = $this->_getCategoryUrlKey($category_path_ids);
            $category_info['path'] = $this->_getCategoryPath($category_path_ids);

            $original_level = $category->getLevel();
            $level = $this->_formatLevelForRootCategory($category->getLevel(),$root_level);
            $category_info['level'] = $level;

            if($original_level == $root_level)
            {
                $category_info['parent_id'] = null;
                $category_info['parent_name'] = null;

                $category_info['root_id'] = $category->getId();
                $category_info['root_name'] = $category->getName();	
            }
            else
            {
                $parent_id = $category->getParentId();
                $category_info['parent_id'] = $parent_id;
                $category_info['parent_name'] = $this->_getParentCategoryName($parent_id);

                $root_id = $this->_getRootId($category_path_ids);
                $category_info['root_id'] = $root_id;
                $category_info['root_name'] = $this->_getParentCategoryName($root_id);	
            }
        }

        return $category_info;
    }
	
    public function _getCategory($category_id)
    {
        $category = null;
        $store_id = $this->getStoreId();
        $category_key = $category_id . '_' . $store_id;
        if(isset($this->_categories[$category_key]))
        {
            $category = $this->_categories[$category_key];			
        }
        else
        {
            $category = Mage::getModel('catalog/category');
            if($store_id)
            {
                $category->setStoreId($store_id);
            }
            $category->load($category_id);
            $this->_categories[$category_key] = $category;			
        }
        return $category;
    }
	
    public function _formatPathIdsForRootCategory($category_path_ids, $root_level)
    {
        $formated_path_ids = $category_path_ids;
        $path_ids_array = explode('/', $category_path_ids);
        if(!empty($path_ids_array))
        {
            for($i=0;$i < $root_level;$i++)
            {
                unset($path_ids_array[$i]);
            }
            $formated_path_ids = implode('/', $path_ids_array);
        }
        return $formated_path_ids;
    }
	
    public function _formatLevelForRootCategory($level, $root_level)
    {
        $formated_level = $level;
        if($root_level > 1)
        {
            $formated_level = $level - $root_level + 1;
        }		 		
        return $formated_level;
    }
	
    public function _getCategoryPath($category_path_ids)
    {		
        $category_path_array = array();
        $path_ids_array = explode('/', $category_path_ids);
        foreach($path_ids_array as $_category_id)
        {
            $category = $this->_getCategory($_category_id);
            $category_path_array[] = $category->getName();
        }
        $category_path = implode('/', $category_path_array);
        return $category_path;
    }
	
    public function _getCategoryUrlKey($category_path_ids)
    {		
        $category_url_key_array = array();
        $path_ids_array = explode('/', $category_path_ids);
        foreach($path_ids_array as $_category_id)
        {
            $category = $this->_getCategory($_category_id);
            $category_url_key_array[] = $category->getUrlKey();
        }
        $category_url_key = implode('/', $category_url_key_array);
        return $category_url_key;
    }
	
    public function _getParentCategoryName($parent_id)
    {
        $parent_name = '';
        $category = $this->_getCategory($parent_id);
        if($category->getId())
        {
            $parent_name = $category->getName();
        }
        return $parent_name;
    }
	
    public function _getRootId($category_path_ids)
    {
        $path_ids_array = explode('/', $category_path_ids);
        if(!empty($path_ids_array))
        {
            $root_id = $path_ids_array[0];
        }
        return $root_id;
    }
}