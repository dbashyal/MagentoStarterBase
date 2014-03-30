<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Poll
 * @package     Technooze_Tpoll
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */


/**
 * Source model for the poll widget configuration
 *
 * @category    Poll
 * @package     Technooze_Tpoll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Technooze_Tpoll_Model_Poll
{

    /**
     * Provides a value-label array of available options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $polls = array();
        $collection = Mage::getModel('poll/poll')
            ->getResourceCollection()
            ->addFieldToFilter('active', 1)
            ->addFieldToFilter('closed', 0)
            ;
        foreach($collection as $poll){
            $polls[] = array('value' => $poll->getData('poll_id'), 'label' => $poll->getData('poll_title'));
        }

        return $polls;
    }

}
