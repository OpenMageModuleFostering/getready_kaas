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
class Getready_Kaas_Model_Product_Api extends Mage_Api_Model_Resource_Abstract
{
    public function info($productId,$storeId)
    {	
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }

        $product_helper = Mage::Helper('kaas_product')->setStoreId($storeId);
        $product = Mage::helper('catalog/product')->getProduct($productId, $storeId, null);
        if (is_null($product->getId())) {
            $this->_fault('product_not_exists');
        }   				

        $product_info = $product_helper->getProductInfo($product); 
        
        Mage::Helper('kaas_activity')->deleteProductActivity($storeId,$productId);
        
        return $product_info;
    }
	
    public function infoBySku($productSku,$storeId)
    {	        
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }
        
        $product_helper = Mage::Helper('kaas_product')->setStoreId($storeId);
        $product_id = Mage::getModel('catalog/product')->getIdBySku($productSku);		
        if($product_id)
        {
            $product = Mage::helper('catalog/product')->getProduct($product_id, $storeId, null);
            if (is_null($product->getId())) {
                $this->_fault('product_not_exists');
            }
        }
        else
        {
            $this->_fault('product_not_exists');
        }

        $product_info = $product_helper->getProductInfo($product);
        
        if($product_id)
        {
            Mage::Helper('kaas_activity')->deleteProductActivity($storeId,$product_id);
        }
        
        return $product_info;
    }
	
    public function items($storeId)
    {	
        $product_helper = Mage::Helper('kaas_product')->setStoreId($storeId);
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('name');		
		
        $result = array();
		
        foreach ($collection as $product) 
        {			
            $product = Mage::helper('catalog/product')->getProduct($product->getId(), $storeId, null);
            if (is_null($product->getId())) {
                $this->_fault('product_not_exists');
            }   
									
            $product_info = $product_helper->getProductInfo($product);
            
            Mage::Helper('kaas_activity')->deleteProductActivity($storeId,$product->getId());
            
            $result[] = $product_info;
        }

        return $result;
    }
	
    public function itemsByIds($storeId, $productIds)
    {
        $product_helper = Mage::Helper('kaas_product')->setStoreId($storeId);
        $result = array();
        $products_export = array();
        $errors = array();        
        if(!empty($productIds))
        {
            foreach($productIds as $product_id)
            {								
                if($product_id > 0)
                {										
                    $product = Mage::helper('catalog/product')->getProduct($product_id, $storeId, null);
                    if (is_null($product->getId())) {                            
                        $errors[] = array(
                            'product_id' => $product_id,
                            'message' => 'product with id ' . $product_id . ' not exists',
                        );
                        continue;
                    }

                    try
                    {
                        $product_info = $product_helper->getProductInfo($product);
                    }
                    catch (Exception $e)
                    {
                        $errors[] = array(
                            'product_id' => $product_id,
                            'message' => $e->getMessage()
                        );
                        continue;
                    }
                    $products_export[] = $product_info;
                    
                    Mage::Helper('kaas_activity')->deleteProductActivity($storeId,$product->getId());
                }
            }
        }

        $result['products'] = $products_export;
        $result['errors'] = $errors;

        return $result;
    }
	
    public function itemsIds($storeId)
    {						
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($storeId);            		

        $result = array();		
        
        foreach ($collection as $product) 
        {           
            $result[] = (int)$product->getId();	
        }

        return $result;
    }
	
	
   
}