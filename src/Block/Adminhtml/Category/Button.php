<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Block\Adminhtml\Category;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Catalog\Model\Category;

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
        /** @var Category $category */
        $category = $this->registry->registry('category');

        if (!$category || !$category->getId()) {
            return null;
        }

        return [
            'name'  => 'page_cache_clean',
            'label' => __('Clean Page Cache'),
            'class' => 'action-secondary',
            'url'   => sprintf(
                'infrangible_page_cache_clean/clean/category/id/%d/store/%d',
                $category->getId(),
                $category->getStoreId()
            )
        ];
    }
}
