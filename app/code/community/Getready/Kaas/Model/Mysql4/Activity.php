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
class Getready_Kaas_Model_Mysql4_Activity extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('kaas/kaas_activity', 'id');
    }
}
