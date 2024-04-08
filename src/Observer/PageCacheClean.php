<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Observer;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\PageCacheClean\Helper\Data;
use Infrangible\PageCacheClean\Model\PageCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class PageCacheClean
    implements ObserverInterface
{
    /** @var Variables */
    protected $variables;

    /** @var Data */
    protected $pageCacheCleanHelper;

    /**
     * @param Variables $variables
     * @param Data      $pageCacheCleanHelper
     */
    public function __construct(Variables $variables, Data $pageCacheCleanHelper)
    {
        $this->variables = $variables;
        $this->pageCacheCleanHelper = $pageCacheCleanHelper;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        $entityType = $event->getData(PageCache::EVENT_ENTITY_TYPE);
        $entityIds = $event->getData(PageCache::EVENT_ENTITY_IDS);
        $storeId = intval($event->getData(PageCache::EVENT_STORE_ID));
        $isTest = boolval($event->getData(PageCache::EVENT_TEST));

        if (!$this->variables->isEmpty($entityType) && !$this->variables->isEmpty($entityIds)) {
            $this->pageCacheCleanHelper->cleanPageCache($entityType, $entityIds, $storeId, $isTest);
        }
    }
}
