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
class Getready_Kaas_Helper_Product_Data extends Mage_Core_Helper_Abstract
{
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

    public function getProductInfo($product)
    {
        $product_info = array(
            //header
            'product_id' => '',
            'group_id' => '',
            'url' => '',
            'created_at' => array('sec' => '', 'usec' => ''),
            'creation_datetime' => '',
            'creation_date' => '',
            'creation_time' => '',
            'updated_at' => array('sec' => '', 'usec' => ''),
            'updated_datetime' => '',
            'updated_date' => '',
            'updated_time' => '',
            'is_visible' => '',
            'is_parent' => '',
            'is_child' => '',
            //flat
            'flat' => array(),
            //price
            'price_original_include_tax' => '',
            'price_original_exclude_tax' => '',
            'price_final_include_tax' => '',
            'price_final_exclude_tax' => '',
            'price_cost' => '',
            'tax_percent' => '',
            'discount' => '',
            //invertory
            'qty' => 0,
            'stock_status' => false,
            //media
            'gallery' => array(),
            //category
            'category' => array(),
            'children' => array(),
        );

        //header
        $product_info['product_id'] = $product->getId();
        $product_info['group_id'] = $this->_getGroupId($product);
        $product_info['url'] = $product->getProductUrl();
        $created_at = $product->getCreatedAt();

        $product_info['created_at'] = array(
            'sec' => strtotime($created_at),
            'usec' => 0,
        );
        $product_info['creation_datetime'] = date('Y-m-d H:i:s',  strtotime($created_at));
        $product_info['creation_date'] = date('Y-m-d',  strtotime($created_at));
        $product_info['creation_time'] = date('H:i:s',  strtotime($created_at));

        $updated_at = $product->getUpdatedAt();
        $product_info['updated_at'] = array(
            'sec' => strtotime($updated_at),
            'usec' => 0,
        );
        $product_info['updated_datetime'] = date('Y-m-d H:i:s',  strtotime($updated_at));
        $product_info['updated_date'] = date('Y-m-d',  strtotime($updated_at));
        $product_info['updated_time'] = date('H:i:s',  strtotime($updated_at));

        $visibility = false;
        if ($product->getVisibility() == '2' || $product->getVisibility() == '4') {
            $visibility = true;
        }
        $product_info['is_visible'] = $visibility;
        $product_info['is_parent'] = $this->_isParent($product);
        $product_info['is_child'] = (bool) $this->_isChild($product, false);

        //flat		
        $flat_attributes = $this->_getFlatAttributes($product);
        $product_info['flat'] = $flat_attributes;

        //price				
        $prices = $this->_getPrices($product);

        $product_info['price_original_include_tax'] = $prices['price_original_include_tax'];
        $product_info['price_original_exclude_tax'] = $prices['price_original_exclude_tax'];
        $product_info['price_final_include_tax'] = $prices['price_final_include_tax'];
        $product_info['price_final_exclude_tax'] = $prices['price_final_exclude_tax'];
        $product_info['price_cost'] = $prices['price_cost'];
        $product_info['tax_percent'] = $prices['tax_percent'];
        $product_info['discount'] = $prices['discount'];

        //inventory
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        //$product_info['qty'] = (int)number_format($stock->getQty(),0);
        $product_info['qty'] = (int) $stock->getQty();
        $product_info['stock_status'] = (boolean) $stock->getIsInStock();

        //media						
        $product_info['gallery'] = $this->_getGallery($product);

        //category
        $product_info['category'] = $this->_getCategory($product);

        //children
        $product_info['children'] = $this->_getChildren($product);

        return $product_info;
    }

    public function _getGroupId($product)
    {
        $group_id = $product->getId();

        $is_parent = $this->_isParent($product);
        $is_child = false;
        if (!$is_parent) {
            $parent_id = $this->_isChild($product, true);
            if ($parent_id) {
                $group_id = $parent_id;
            }
        }

        return $group_id;
    }

    public function _isParent($product)
    {
        $is_parent = false;
        $product_id = $product->getId();

        if ($product->getTypeId() != 'simple') {
            $child_ids = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product_id);
            if (!$child_ids) {
                $child_ids = Mage::getModel('catalog/product_type_grouped')->getChildrenIds($product_id);
            }

            // if(!$child_ids)
            // {
            //          $child_ids = Mage::getModel('bundle/product_type')->getChildrenIds($product_id); 
            // }

            if ($child_ids) {
                $is_parent = true;
            }
        }

        return $is_parent;
    }

    public function _isChild($product, $return_parent_id = false)
    {
        $is_child = false;
        $product_id = $product->getId();

        if ($product->getTypeId() == 'simple') {
            $parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product_id);
            if (!$parent_ids) {
                $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
            }

            // if(!$parent_ids)
            // {
            //          $parent_ids = $parent_ids = Mage::getModel('bundle/product_type')->getParentIdsByChild($product_id);
            // }

            if ($parent_ids) {
                if ($return_parent_id) {
                    $is_child = reset($parent_ids);
                } else {
                    $is_child = true;
                }
            }
        }

        return $is_child;
    }

    public function _getChildren($product)
    {
        $children = array();
        $product_id = $product->getId();

        if ($product->getTypeId() != 'simple') {
            $conf_child_ids = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product_id);
            if (isset($conf_child_ids[0])) {
                $child_ids = $conf_child_ids[0];
                foreach ($child_ids as $child_id) {
                    $children[] = $child_id;
                }
            }

            $group_child_ids = Mage::getModel('catalog/product_type_grouped')->getChildrenIds($product_id);
            if (isset($group_child_ids[Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED])) {
                $child_ids = $group_child_ids[Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED];
                foreach ($child_ids as $child_id) {
                    $children[] = $child_id;
                }
            }

            // $bundle_child_ids = Mage::getModel('bundle/product_type')->getChildrenIds($product_id); 
            // foreach($bundle_child_ids as $child_id)
            // {
            //          $children[] = $child_id;
            // }			
        }

        return $children;
    }

    public function _getFlatAttributes($product)
    {
        $flat_attributes = array();
        $attributes = $product->getAttributes();
        $not_exported_attributes_codes = array(
            'type_id',
            'entity_id',
            'attribute_set_id',
            'entity_type_id',
            'old_id',
            'url_path',
            'category_ids',
            'required_options',
            'has_options',
            'created_at',
            'updated_at',
            'media_gallery',
            'gallery',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'page_layout',
            'options_container',
            'thumbnail_label',
            'image_label',
            'small_image_label',
        );
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!in_array($attributeCode, $not_exported_attributes_codes)) {
                $label = $attribute->getStoreLabel($product->getStoreId());
                $value = $attribute->getFrontend()->getValue($product);

                $flat_attribute = array(
                    'code' => $attributeCode,
                    'label' => $label,
                    'value' => $this->_getProductAttributeValue($product, $attribute),
                );

                $flat_attributes[] = $flat_attribute;
            }
        }

        return $flat_attributes;
    }

    protected function _normalizeFormatedValue($formated_value)
    {
        // check utf8
        $formated_value = @iconv('UTF-8', 'UTF-8//IGNORE', $formated_value);
        // remove not printed chars and 4+bytes symbols
        $formated_value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F\x{10000}-\x{10FFFF}]/u', '', $formated_value);

        return $formated_value;
    }

    public function _getProductAttributeValue($product, $attribute)
    {
        $formated_value = $attribute->getFrontend()->getValue($product);

        $attributeCode = $attribute->getAttributeCode();
        if ($attributeCode == 'image') {
            $base_image = $product->getImage();
            if ($base_image) {
                $formated_value = Mage::getModel('catalog/product_media_config')->getMediaUrl($base_image);
            }
        } elseif ($attributeCode == 'small_image') {
            $small_image = $product->getSmallImage();
            if ($small_image) {
                $formated_value = Mage::getModel('catalog/product_media_config')->getMediaUrl($small_image);
            }
        } elseif ($attributeCode == 'thumbnail') {
            $thumbnail = $product->getThumbnail();
            if ($thumbnail) {
                $formated_value = Mage::getModel('catalog/product_media_config')->getMediaUrl($thumbnail);
            }
        }

        $backend_type = $attribute->getBackendType();
        switch ($backend_type) {
            case 'decimal':
                $formated_value = number_format($formated_value, 2);
                break;
            case 'datetime':
                $formated_value = $this->_format_datetime($formated_value);
                break;
            default:
                $formated_value = $this->_normalizeFormatedValue($formated_value);

        }

        return $formated_value;
    }

    public function _getPrices($product)
    {
        $prices = array(
            'price_original_include_tax' => 0,
            'price_original_exclude_tax' => 0,
            'price_final_include_tax' => 0,
            'price_final_exclude_tax' => 0,
            'price_cost' => 0,
            'tax_percent' => 0,
            'discount' => 0,
        );

        //price
        $tax_helper = Mage::Helper('tax');

        $_price = $product->getPrice();
        $_final_price = $product->getFinalPrice();

        $price = $tax_helper->getPrice($product, $_price);
        $price_incl_tax = $tax_helper->getPrice($product, $_price, true);
        $final_price = $tax_helper->getPrice($product, $_final_price);
        $final_price_incl_tax = $tax_helper->getPrice($product, $_final_price, true);

        $prices['price_original_include_tax'] = (double) $price_incl_tax; //number_format($price_incl_tax,2);
        $prices['price_original_exclude_tax'] = (double) $price; //number_format($price,2);
        $prices['price_final_include_tax'] = (double) $final_price_incl_tax; //number_format($final_price_incl_tax,2);
        $prices['price_final_exclude_tax'] = (double) $final_price; //number_format($final_price,2);

        //cost
        $prices['price_cost'] = (double) number_format($product->getCost(), 2);

        //tax
        $prices['tax_percent'] = (double) $this->_getTax($product);

        //discount
        $discount = abs((double) $price_incl_tax - (double) $final_price_incl_tax);
        $prices['discount'] = (double) number_format($discount, 2);

        return $prices;
    }

    public function _getTax($product)
    {
        $tax = 0;
        $store_id = $product->getStoreId();

        if ($store_id) {
            $store = Mage::app()->getStore($store_id);
            if ($store->getId()) {
                $taxCalculation = Mage::getModel('tax/calculation');
                $request = $taxCalculation->getRateRequest(null, null, null, $store);
                $taxClassId = $product->getTaxClassId();
                $tax = $taxCalculation->getRate($request->setProductClassId($taxClassId));
            }
        }

        return $tax;
    }

    //nepouziva se
    public function _getImages($product)
    {
        $images = array(
            'thumbnail' => '',
            'small_image' => '',
            'image' => '',
        );

        $thumbnail = $product->getThumbnail();
        if ($thumbnail) {
            $images['thumbnail'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($thumbnail); //(string)Mage::Helper('catalog/image')->init($product, 'thumbnail');
        }
        $small_image = $product->getSmallImage();
        if ($small_image) {
            $images['small_image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($small_image); //(string)Mage::Helper('catalog/image')->init($product, 'small_image');
        }
        $base_image = $product->getImage();
        if ($base_image) {
            $images['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($base_image); //(string)Mage::Helper('catalog/image')->init($product, 'image');
        }

        return $images;
    }

    public function _getGallery($product)
    {
        $gallery = array();

        foreach ($product->getMediaGalleryImages() as $image) {
            $product_image = array(
                'label' => $image->getLabel(),
                'thumbnail_image' => $image->getUrl(),
                'small_image' => $image->getUrl(),
                'base_image' => $image->getUrl(),
            );
            $gallery[] = $product_image;
        }

        return $gallery;
    }

    public function _getCategory($product)
    {
        $category = array();
        $product_category_helper = Mage::Helper('kaas_product_category')->setStoreId($this->getStoreId());

        $category_ids = $product->getCategoryIds();
        foreach ($category_ids as $category_id) {
            if (Mage::Helper('kaas_product_store')->isStoreCategory($category_id, $product->getStoreId())) {
                $category_info = $product_category_helper->getCategoryInfo($category_id);
                if ($category_info && isset($category_info['level']) && $category_info['level'] > 0) {
                    $category[] = $category_info;
                }
            }
        }

        return $category;
    }

    public function _format_datetime($datetime)
    {
        $formated_value = $datetime;
        if ($datetime) {
            $formated_value = date('Y-m-d H:i:s',  strtotime($datetime));
        }

        return $formated_value;
    }
}
