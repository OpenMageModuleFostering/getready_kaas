<?php
/**
 * Magento Module developed by Getready s.r.o.
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
 * @copyright  Copyright (c) 2016 Getready s.r.o. (http://getready.cz)
 */
/**
 * @category   Getready
 *
 * @author     Getready Team <info@getready.cz>
 */
class Getready_Kaas_Model_Observer
{
    public function addUpdateActivity(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $product_id = $product->getId();
            $store_id = $product->getStoreId();
            if ($product && $product->getId()) {
                Mage::Helper('kaas_activity')->createUpdateActivity($store_id, $product_id);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function addDeleteActivity(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product && $product->getId()) {
                $product_id = $product->getId();
                Mage::Helper('kaas_activity')->createDeleteActivity($product_id);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function addStockActivity(Varien_Event_Observer $observer)
    {
        try {
            $quote = $observer->getEvent()->getQuote();

            //$store_id = $quote->getStoreId();

            $items = $quote->getAllItems();

            Mage::Helper('kaas_activity')->createStockActivity($items);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }
}
