<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Block\Adminhtml\Product;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Catalog\Model\Product;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Button
    extends Generic
{
    public function getButtonData(): ?array
    {
        /** @var Product $product */
        $product = $this->registry->registry('product');

        if (!$product || !$product->getId()) {
            return null;
        }

        return [
            'name'  => 'page_cache_clean',
            'label' => __('Clean Page Cache'),
            'class' => 'action-secondary',
            'url'   => sprintf(
                'infrangible_page_cache_clean/clean/product/id/%d/set/%d/store/%d',
                $product->getId(),
                $product->getAttributeSetId(),
                $product->getStoreId()
            )
        ];
    }
}
