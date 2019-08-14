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
class Getready_Kaas_Model_Store_Api extends Mage_Api_Model_Resource_Abstract
{
	public function info($storeId)
	{
		// Retrieve store info
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }
		
		$store_info = Mage::Helper('kaas_store')->getStoreInfo($store); 
		return $store_info;
	}
	
	public function infoByCode($storeCode)
	{
		// Retrieve store info
        try {            
			$store = Mage::Helper('kaas_store')->getStoreByCode($storeCode);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }
		
		$store_info = Mage::Helper('kaas_store')->getStoreInfo($store); 
		return $store_info;
	}
	
	public function items()
	{
		// Retrieve stores
        $stores = Mage::app()->getStores();

        // Make result array
        $result = array();
        foreach ($stores as $store) {
            $result[] = Mage::Helper('kaas_store')->getStoreInfo($store);
        }

        return $result;
	}
   
}