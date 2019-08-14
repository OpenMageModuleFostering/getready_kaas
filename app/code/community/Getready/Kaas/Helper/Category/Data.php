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
class Getready_Kaas_Helper_Category_Data extends Mage_Core_Helper_Abstract
{	
	
	public function getCategoryInfo($category,$root_level = 0)
	{
		$category_info =  array(
			'category_id' => '',
			'url' => '',
			'path' => '',
			'path_ids' => '',
			'path_url_key' => '',
			'level' => '',
			'parent_id' => '',
			'parent_name' => '',			
			'root_id' => '',
			'root_name' => '',
			'description' => '',
			'name' => '',			
		);
				
		
		//general
		$category_info['category_id'] = $category->getId();
				        		
		//kaas						
		$category_info['url'] = $category->getUrl();
				
		$category_path_ids = $this->formatPathIdsForRootCategory($category->getPath(),$root_level);
				
		$category_info['path'] = $this->getCategoryPath($category_path_ids);
		$category_info['path_ids'] = $category_path_ids;
		$category_info['path_url_key'] = $this->getCategoryUrlKey($category_path_ids); 
		
		$level = $this->formatLevelForRootCategory($category->getLevel(),$root_level);
		$category_info['level'] = $level; 
		if($level == $root_level)
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
			$category_info['parent_name'] = $this->getCategoryName($parent_id);
			$root_id = $this->getRootId($category_path_ids);
			$category_info['root_id'] = $root_id;
			$category_info['root_name'] = $this->getCategoryName($root_id);
		}
		
		$category_info['description'] = $category->getDescription();
		$category_info['name'] = $category->getName();
						
		return $category_info;
	}	
	
	public function getCategory($category_id)
	{		
		return Mage::Helper('kaas_category_cache')->getCategory($category_id);
	}
	
	public function formatPathIdsForRootCategory($category_path_ids, $root_level)
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
	
	public function formatLevelForRootCategory($level, $root_level)
	{
		$formated_level = $level;
		if($root_level > 1)
		{
			$formated_level = $level - $root_level + 1;
		}		 		
		return $formated_level;
	}
	
	public function getCategoryPath($category_path_ids)
	{		
		$category_path_array = array();
		$path_ids_array = explode('/', $category_path_ids);
		foreach($path_ids_array as $_category_id)
		{
			$category = $this->getCategory($_category_id);
			$category_path_array[] = $category->getName();
		}
		$category_path = implode('/', $category_path_array);
		return $category_path;
	}
	
	public function getCategoryUrlKey($category_path_ids)
	{		
		$category_url_key_array = array();
		$path_ids_array = explode('/', $category_path_ids);
		foreach($path_ids_array as $_category_id)
		{
			$category = $this->getCategory($_category_id);
			$category_url_key_array[] = $category->getUrlKey();
		}
		$category_url_key = implode('/', $category_url_key_array);
		return $category_url_key;
	}
	
	public function getCategoryName($parent_id)
	{
		$parent_name = '';
		$category = $this->getCategory($parent_id);
		if($category->getId())
		{
			$parent_name = $category->getName();
		}
		return $parent_name;
	}
	
	public function getRootId($category_path_ids)
	{
		$path_ids_array = explode('/', $category_path_ids);
		if(!empty($path_ids_array))
		{
			$root_id = $path_ids_array[0];
		}
		return $root_id;
	}
		
}