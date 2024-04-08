<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Helper;

use Infrangible\Core\Helper\Database;
use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var LoggerInterface */
    protected $logging;

    /** @var Config */
    protected $config;

    /** @var \Infrangible\Core\Helper\Category */
    protected $categoryHelper;

    /** @var Database */
    protected $databaseHelper;

    /** @var PurgeCache */
    protected $purgeCache;

    /** @var Type */
    private $fullPageCache;

    /**
     * @param LoggerInterface                   $logging
     * @param Config                            $config
     * @param \Infrangible\Core\Helper\Category $categoryHelper
     * @param Database                          $databaseHelper
     * @param PurgeCache                        $purgeCache
     */
    public function __construct(
        LoggerInterface $logging,
        Config $config,
        \Infrangible\Core\Helper\Category $categoryHelper,
        Database $databaseHelper,
        PurgeCache $purgeCache
    ) {
        $this->logging = $logging;
        $this->config = $config;
        $this->categoryHelper = $categoryHelper;
        $this->databaseHelper = $databaseHelper;
        $this->purgeCache = $purgeCache;
    }

    /**
     * @throws LocalizedException
     * @throws \Exception
     */
    public function cleanPageCache(string $entityType, array $entityIds, int $storeId, bool $isTest = false)
    {
        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        $entityIds = array_unique($entityIds);

        $categoryIds = [];

        if ($entityType === Product::ENTITY) {
            $this->logging->debug(sprintf('Purging cache entries of %d articles', count($entityIds)));

            if ($this->config->getType() == Config::BUILT_IN && $this->config->isEnabled()) {
                $tags = [];

                foreach ($entityIds as $entityId) {
                    if ($storeId === 0) {
                        $tags[] = sprintf('%s_%s', Product::CACHE_TAG, $entityId);
                    } else {
                        $tags[] = sprintf('%s_%s_%s', Product::CACHE_TAG, $entityId, $storeId);
                    }
                }

                if (!$isTest && !empty($tags)) {
                    $this->getCache()->clean('matchingAnyTag', array_unique($tags));
                }
            }

            if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled()) {
                $bareTags = [];

                foreach ($entityIds as $entityId) {
                    if ($storeId === 0) {
                        $bareTags[] = sprintf('%s_%s', Product::CACHE_TAG, $entityId);
                    } else {
                        $bareTags[] = sprintf('%s_%s_%s', Product::CACHE_TAG, $entityId, $storeId);
                    }
                }

                $tags = [];

                foreach ($bareTags as $tag) {
                    $tags[] = sprintf('((^|,)%s(,|$))', $tag);
                }

                if (!$isTest && !empty($tags)) {
                    $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
                }
            }

            $this->logging->info(sprintf('Purged cache entries of %d articles', count($entityIds)));

            $categoryIds = $this->categoryHelper->getEntityIds($dbAdapter, $entityIds, false);
        }

        if ($entityType === Category::ENTITY) {
            $categoryIds = array_merge($categoryIds, $entityIds);
        }

        if (count($categoryIds) > 0) {
            foreach ($this->categoryHelper->getPathEntityIds($dbAdapter, $categoryIds) as $categoryPathIds) {
                $categoryIds = array_merge($categoryIds, $categoryPathIds);
            }

            $categoryIds = array_unique($categoryIds);

            $this->logging->debug(sprintf('Purging cache entries of %d categories', count($categoryIds)));

            if ($this->config->getType() == Config::BUILT_IN && $this->config->isEnabled()) {
                $tags = [];

                foreach ($categoryIds as $categoryId) {
                    if ($storeId === 0) {
                        $tags[] = sprintf('%s_%s', Category::CACHE_TAG, $categoryId);
                        $tags[] = sprintf('%s_%s', Product::CACHE_PRODUCT_CATEGORY_TAG, $categoryId);
                    } else {
                        $tags[] = sprintf('%s_%s_%s', Category::CACHE_TAG, $categoryId, $storeId);
                        $tags[] = sprintf('%s_%s_%s', Product::CACHE_PRODUCT_CATEGORY_TAG, $categoryId, $storeId);
                    }
                }

                if (!$isTest && !empty($tags)) {
                    $this->getCache()->clean('matchingAnyTag', array_unique($tags));
                }
            }

            if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled()) {
                $bareTags = [];

                foreach ($categoryIds as $categoryId) {
                    if ($storeId === 0) {
                        $bareTags[] = sprintf('%s_%s', Category::CACHE_TAG, $categoryId);
                        $bareTags[] = sprintf('%s_%s', Product::CACHE_PRODUCT_CATEGORY_TAG, $categoryId);
                    } else {
                        $bareTags[] = sprintf('%s_%s_%s', Category::CACHE_TAG, $categoryId, $storeId);
                        $bareTags[] = sprintf('%s_%s_%s', Product::CACHE_PRODUCT_CATEGORY_TAG, $categoryId, $storeId);
                    }
                }

                $tags = [];

                foreach ($bareTags as $tag) {
                    $tags[] = sprintf('((^|,)%s(,|$))', $tag);
                }

                if (!$isTest && !empty($tags)) {
                    $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
                }
            }

            $this->logging->info(sprintf('Purged cache entries of %d categories', count($categoryIds)));
        }
    }

    /**
     * @return Type
     */
    private function getCache(): Type
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = ObjectManager::getInstance()->get(Type::class);
        }

        return $this->fullPageCache;
    }
}
