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
class Getready_Kaas_Helper_Activity_Data extends Mage_Core_Helper_Abstract
{
    protected $_storeIds = null;

    public function getActivityFeed($store)
    {
        $activity_feed = array();

        $store_id = $store->getId();

        $collection = $this->getActivityCollection($store_id);
        foreach ($collection as $item) {
            $activity_feed[] = array(
                'entity_id' => (int) $item->getProductId(),
                'action' => $item->getaction(),
            );
        }

        return $activity_feed;
    }

    public function getAllStoreIds()
    {
        if ($this->_storeIds == null) {
            $stores = Mage::app()->getStores();

            $ids = array();
            foreach ($stores as $store) {
                $ids[] = $store->getId();
            }

            $this->_storeIds = $ids;
        }

        return $this->_storeIds;
    }

    public function createUpdateActivity($store_id, $product_id)
    {
        if ($product_id) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $write_connection = $resource->getConnection('core_write');
                $table_name = $resource->getTableName('getready_kaas_activity');

                $action = 'update';
                $created_at = now();
                $data = array();

                if ($store_id) {
                    //delete all activity by product id for specific store
                    $sql = "DELETE FROM `{$table_name}`  WHERE store_id = :store_id AND product_id = :product_id ";
                    $binds = array(
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                    );
                    $write_connection->query($sql, $binds);

                    $data[] = array(
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                        'action' => $action,
                        'created_at' => $created_at,
                    );
                } elseif ($store_id == 0) {
                    //delete all activity by product id for all stores
                    $sql = "DELETE FROM `{$table_name}`  WHERE product_id = :product_id ";
                    $binds = array(
                        'product_id' => $product_id,
                    );
                    $write_connection->query($sql, $binds);

                    foreach ($this->getAllStoreIds() as $_store_id) {
                        $data[] = array(
                            'store_id' => $_store_id,
                            'product_id' => $product_id,
                            'action' => $action,
                            'created_at' => $created_at,
                        );
                    }
                }

                if (count($data) > 0) {
                    $write_connection->insertMultiple($table_name, $data);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function createDeleteActivity($product_id)
    {
        if ($product_id) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $write_connection = $resource->getConnection('core_write');
                $table_name = $resource->getTableName('getready_kaas_activity');

                //delete all activity by product id
                $sql = "DELETE FROM `{$table_name}`  WHERE product_id = :product_id ";
                $binds = array(
                    'product_id' => $product_id,
                );
                $write_connection->query($sql, $binds);

                //add for all stores
                $action = 'delete';
                $created_at = now();
                $data = array();
                foreach ($this->getAllStoreIds() as $store_id) {
                    $data[] = array(
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                        'action' => $action,
                        'created_at' => $created_at,
                    );
                }
                if (count($data) > 0) {
                    $write_connection->insertMultiple($table_name, $data);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function createStockActivity($quote_items)
    {
        try {
            $resource = Mage::getSingleton('core/resource');
            $write_connection = $resource->getConnection('core_write');
            $table_name = $resource->getTableName('getready_kaas_activity');

            $store_ids = $this->getAllStoreIds();

            $products_ids = array();
            foreach ($quote_items as $item) {
                $products_ids[] = $item->getProductId();
            }

            //delete all activity by product ids
            if (count($products_ids) > 0) {
                $product_ids_string = implode(',', $products_ids);
                $sql = "DELETE FROM `{$table_name}`  WHERE product_id IN (:product_ids)";
                $binds = array(
                    'product_ids' => $product_ids_string,
                );
                $write_connection->query($sql, $binds);
            }

            //add new activities
            $action = 'update';
            $created_at = now();
            $data = array();
            foreach ($quote_items as $item) {
                $_product_id = $item->getProductId();
                foreach ($store_ids as $_store_id) {
                    $data[] = array(
                        'store_id' => $_store_id,
                        'product_id' => $_product_id,
                        'action' => $action,
                        'created_at' => $created_at,
                    );
                }
            }
            if (count($data) > 0) {
                $write_connection->insertMultiple($table_name, $data);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function deleteProductActivity($store_id, $product_id)
    {
        if ($store_id && $product_id) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $write_connection = $resource->getConnection('core_write');
                $table_name = $resource->getTableName('getready_kaas_activity');

                $sql = "DELETE FROM `{$table_name}`  WHERE store_id = :store_id AND product_id = :product_id ";
                $binds = array(
                    'store_id' => $store_id,
                    'product_id' => $product_id,
                );
                $write_connection->query($sql, $binds);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function getActivityCollection($store_id)
    {
        $collection = Mage::getModel('kaas/activity')->getCollection();
        $collection->addFieldToFilter('store_id', $store_id);

            //$collection->addFieldToFilter('store_id', array('in' => array(0,$store_id)) );

            return $collection;
    }

    public function clearActivityFeed($store)
    {
        $clered = false;

        $store_id = $store->getId();

        if ($store_id) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $write_connection = $resource->getConnection('core_write');
                $table_name = $resource->getTableName('getready_kaas_activity');

                $sql = "DELETE FROM `{$table_name}`  WHERE store_id = :store_id ";
                $binds = array(
                    'store_id' => $store_id,
                );
                $write_connection->query($sql, $binds);
                $clered = true;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $clered;
    }
}
