<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Plugin\Catalog\Block\Product;

use Magento\Catalog\Model\Product;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class View
{
    public function afterGetIdentities(\Magento\Catalog\Block\Product\View $subject, array $result): array
    {
        $product = $subject->getProduct();

        if ($product && $product->getId()) {
            $productId = $product->getId();
            $storeId = $product->getStoreId();

            $result[] = sprintf('%s_%d_%d', Product::CACHE_TAG, $productId, $storeId);
        }

        return $result;
    }
}
