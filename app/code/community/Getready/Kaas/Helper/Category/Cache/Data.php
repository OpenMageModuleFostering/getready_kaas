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
class Getready_Kaas_Helper_Category_Cache_Data extends Mage_Core_Helper_Abstract
{
    protected $_categories = array();

    public function loadCategory($category_id, $store_id = 0)
    {
        $category = Mage::getModel('catalog/category');
        if ($store_id) {
            $category->setStoreId($store_id);
        }
        $category->load($category_id);

        return $category;
    }

    public function getCategory($category_id, $store_id = 0)
    {
        $category = null;
        $category_key = $category_id.'_'.$store_id;
        if (isset($this->_categories[$category_key])) {
            $category = $this->_categories[$category_key];
        } else {
            $category = $this->loadCategory($category_id, $store_id);
            $this->_categories[$category_key] = $category;
        }

        return $category;
    }
}
