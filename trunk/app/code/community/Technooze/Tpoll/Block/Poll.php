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
 * Widget which displays the polls list
 *
 * @category    Poll
 * @package     Technooze_Tpoll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Technooze_Tpoll_Block_Poll
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    /**
     * A model to serialize attributes
     * @var Varien_Object
     */
    protected $_serializer = null;

    /**
     * Poll model
     *
     * @var Mage_Poll_Model_Poll
     */
    protected $_pollModel;

    /**
     * Poll id
     *
     * @var int
     */
    protected $_pollId = 0;


    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->_serializer = new Varien_Object();
        $this->_pollModel = Mage::getModel('poll/poll');
        parent::_construct();
    }

    /**
     * Get Poll related data
     *
     * @param int $pollId
     * @return array|bool
     */
    public function getPollData($pollId)
    {
        if (empty($pollId)) {
            return false;
        }
        $poll = $this->_pollModel->load($pollId);

        $pollAnswers = Mage::getModel('poll/poll_answer')
            ->getResourceCollection()
            ->addPollFilter($pollId)
            ->load()
            ->countPercent($poll);

        // correct rounded percents to be always equal 100
        $percentsSorted = array();
        $answersArr = array();
        foreach ($pollAnswers as $key => $answer) {
            $percentsSorted[$key] = $answer->getPercent();
            $answersArr[$key] = $answer;
        }
        asort($percentsSorted);
        $total = 0;
        foreach ($percentsSorted as $key => $value) {
            $total += $value;
        }
        // change the max value only
        if ($total > 0 && $total !== 100) {
            $answersArr[$key]->setPercent($value + 100 - $total);
        }

        return array(
            'poll' => $poll,
            'poll_answers' => $pollAnswers,
            'action' => Mage::getUrl('poll/vote/add', array('poll_id' => $pollId, '_secure' => true))
        );
    }

    /**
     * Produces poll html
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $coreSessionModel Mage_Core_Model_Session */
        $coreSessionModel = Mage::getSingleton('core/session');
        $justVotedPollId = $coreSessionModel->getJustVotedPoll();
        if ($justVotedPollId && !$this->_pollModel->isVoted($justVotedPollId)) {
            $this->_pollModel->setVoted($justVotedPollId);
        }
        $coreSessionModel->setJustVotedPoll(false);

        $html = '';
        $config = $this->getData('enabled_poll');
        if (empty($config)) {
            return $html;
        }
        $polls = explode(',', $config);
        $list = array();
        foreach ($polls as $poll) {
            $this->_pollId = $poll;
            $item = $this->getPollData($poll);
            if ($item) {
                $list[] = $item;
            }
        }
        $this->assign('polls', $list);

        if ($this->_pollModel->isVoted($this->_pollId) === true || $justVotedPollId) {
            $this->setTemplate('technooze/tpoll/result.phtml');
        }

        return parent::_toHtml();
    }

    /**
     * Generates poll
     *
     * @param string $poll
     * @return array
     */
    protected function _generatePoll($poll)
    {
        /**
         * Current URL
         */
         //$currentUrl = $this->getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));

        /**
         * Link HTML
         */
        $attributes = array();
        $icon = '';


        return $icon;
    }

}
