<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Model;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\Event\Manager;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class PageCache
{
    /** @var string */
    public const EVENT_ENTITY_IDS = 'entity_ids';

    /** @var string */
    public const EVENT_ENTITY_TYPE = 'entity_type';

    /** @var string */
    public const EVENT_STORE_ID = 'store_id';

    /** @var string */
    public const EVENT_TEST = 'test';

    /** @var LoggerInterface */
    protected $logger;

    /** @var Manager */
    protected $eventManager;

    /** @var Variables */
    protected $variables;

    /** @var Stores */
    protected $storeHelper;

    /** @var bool */
    private $test = false;

    /** @var array */
    private $cacheEvents = [];

    /**
     * @param Variables $variables
     * @param Stores $storeHelper
     * @param LoggerInterface $logger
     * @param Manager $eventManager
     */
    public function __construct(
        Variables $variables,
        Stores $storeHelper,
        LoggerInterface $logger,
        Manager $eventManager
    ) {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->variables = $variables;
        $this->storeHelper = $storeHelper;
    }

    /**
     * @return  bool
     */

    public function isTest(): bool
    {
        return $this->test === true;
    }

    /**
     * @param bool $test
     *
     * @return  void
     */

    public function setTest(bool $test = true)
    {
        $this->test = $test;
    }

    /**
     * @return array
     */
    public function getCacheEvents(): array
    {
        return $this->cacheEvents;
    }

    /**
     * @param string $entityType
     * @param int    $entityId
     * @param int    $storeId
     *
     * @throws \Exception
     */
    public function addCacheEvent(string $entityType, int $entityId, int $storeId)
    {
        if (empty($entityType)) {
            throw new \Exception('Trying to add cache event without any entity type');
        }

        if (empty($entityId)) {
            throw new \Exception(
                sprintf('Trying to add cache event without entity id for entity type: %s', $entityType)
            );
        }

        if (!is_numeric($entityId)) {
            throw new \Exception(
                sprintf(
                    'Trying to add cache event with invalid entity id: %s for entity type: %s',
                    $entityId,
                    $entityType
                )
            );
        }

        if ($this->variables->isEmpty($storeId)) {
            throw new \Exception(
                sprintf(
                    'Trying to add cache event without store id for entity type: %s and entity id: %d',
                    $entityType,
                    $entityId
                )
            );
        }

        if (!is_numeric($storeId)) {
            throw new \Exception(
                sprintf(
                    'Trying to add cache event with invalid store id: %s for entity type: %s and entity id: %d',
                    $storeId,
                    $entityType,
                    $entityId
                )
            );
        }

        if ($storeId == 0) {
            foreach ($this->storeHelper->getStores() as $store) {
                $storeId = $store->getId();
                if (is_string($storeId)) {
                    $storeId = intval($storeId);
                }
                $this->addCacheEvent($entityType, $entityId, $storeId);
            }

            return;
        }

        if (!array_key_exists($entityType, $this->cacheEvents)) {
            $this->cacheEvents[$entityType] = [];
        }

        if (!array_key_exists($storeId, $this->cacheEvents[$entityType])) {
            $this->cacheEvents[$entityType][$storeId] = [];
        }

        if (!in_array($entityId, $this->cacheEvents[$entityType][$storeId])) {
            $this->cacheEvents[$entityType][$storeId][] = $entityId;

            $this->logger->debug(
                'Article with id: %d in store with id: %d requires site cache change', [$entityId, $storeId]
            );
        }
    }

    /**
     * Process all cache events.
     */
    public function clean()
    {
        foreach ($this->cacheEvents as $entityType => $entityTypeData) {
            $this->cleanEntityType($entityType);
        }
    }

    /**
     * @param string $entityType
     */
    protected function cleanEntityType(string $entityType)
    {
        if (!array_key_exists($entityType, $this->cacheEvents)) {
            return;
        }

        foreach ($this->cacheEvents[$entityType] as $storeId => $entityIds) {
            $this->logger->debug(
                'Processing cache cleaning for entity type: %s, store with id: %d and %d entity(ies)',
                [$entityType, $storeId, count($entityIds)]
            );

            $this->eventManager->dispatch('page_cache_clean', [
                static::EVENT_ENTITY_TYPE => $entityType,
                static::EVENT_STORE_ID    => $storeId,
                static::EVENT_ENTITY_IDS  => $entityIds,
                static::EVENT_TEST        => $this->isTest()
            ]);
        }
    }
}
