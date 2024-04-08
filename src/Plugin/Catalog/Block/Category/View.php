<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Plugin\Catalog\Block\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class View
{
    public function afterGetIdentities(\Magento\Catalog\Block\Category\View $subject, array $result): array
    {
        $category = $subject->getCurrentCategory();

        if ($category && $category->getId()) {
            $categoryId = $category->getId();
            $storeId = $category->getStoreId();

            $result[] = sprintf('%s_%d_%d', Category::CACHE_TAG, $categoryId, $storeId);
            $result[] = sprintf('%s_%d_%d', Product::CACHE_PRODUCT_CATEGORY_TAG, $categoryId, $storeId);
        }

        return $result;
    }
}
