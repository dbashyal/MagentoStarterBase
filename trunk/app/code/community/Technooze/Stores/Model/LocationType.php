<?php
/**
 * Technooze_Stores extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Technooze
 * @package    Technooze_Stores
 * @copyright  Copyright (c) 2008 Technooze LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Technooze
 * @package    Technooze_Stores
 * @author     Technooze <info@technooze.com>
 */
class Technooze_Stores_Model_LocationType extends Varien_Object
{
    const STOCKIST  = 0;
    const MARKET    = 1;
    const EVENT     = 2;

    static public function getOptionArray()
    {
        return array(
            self::STOCKIST  => Mage::helper('stores')->__('Stockist'),
            self::MARKET    => Mage::helper('stores')->__('Market'),
            self::EVENT     => Mage::helper('stores')->__('Event')
        );
    }
}
