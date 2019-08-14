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
class Getready_Kaas_Helper_Store_Data extends Mage_Core_Helper_Abstract
{
    public function getStoreInfo($store)
    {
        $store_info = array(
            'store_id' => '',
            'code' => '',
            'website_id' => '',
            'group_id' => '',
            'name' => '',
            'sort_order' => '',
            'is_active' => '',
            'country' => '',
            'language' => '',
            'locale' => '',
            'currency' => '',
            'currency_symbol' => '',
            'timezone' => '',
            'store_url' => '',
            'store_url_secure' => '',
            'ga_account' => '',
            'kaas_module_version' => '',
        );

        //general
        $store_info['store_id'] = $store->getId();
        $store_info['code'] = $store->getCode();
        $store_info['website_id'] = $store->getWebsiteId();
        $store_info['group_id'] = $store->getGroupId();
        $store_info['name'] = $store->getName();
        $store_info['sort_order'] = $store->getSortOrder();
        $store_info['is_active'] = $store->getIsActive();

        //kaas
        $store_id = $store->getId();
        $store_info['country'] = Mage::getStoreConfig('general/country/default', $store_id);
        $store_info['language'] = strtoupper(substr(Mage::getStoreConfig('general/locale/code', $store_id), 0, 2));
        $store_info['locale'] = Mage::getStoreConfig('general/locale/code', $store_id);
        $currency = Mage::getStoreConfig('currency/options/default', $store_id);
        $store_info['currency'] = $currency;
        $store_info['currency_symbol'] = Mage::app()->getLocale()->currency($currency)->getSymbol();
        //$timezone_string = Mage::getStoreConfig('general/locale/timezone', $store_id);
        $date = Mage::app()->getLocale()->storeDate($store_id);
        $store_info['timezone'] = $date->get(Zend_Date::GMT_DIFF);
        $store_info['store_url'] = Mage::getStoreConfig('web/unsecure/base_url', $store_id);
        $store_info['store_url_secure'] = Mage::getStoreConfig('web/secure/base_url', $store_id);

        $store_info['ga_account'] = trim((string) Mage::getStoreConfig('google/analytics/account', $store_id));

        $store_info['kaas_module_version'] = (string) Mage::helper('kaas/data')->getModuleVersion();

        return $store_info;
    }

    public function getStoreByCode($store_code)
    {
        $store_id = null;
        $stores = array_keys(Mage::app()->getStores());
        foreach ($stores as $id) {
            $store = Mage::app()->getStore($id);
            if ($store->getCode() == $store_code) {
                $store_id = $id;
            }
        }

        return Mage::app()->getStore($store_id);
    }
}
