<?php

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Controller\Adminhtml\Clean;

use Infrangible\PageCacheClean\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
    extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /** @var Data */
    protected $pageCacheCleanHelper;

    public function __construct(Context $context, Builder $productBuilder, Data $pageCacheCleanHelper)
    {
        parent::__construct($context, $productBuilder);

        $this->pageCacheCleanHelper = $pageCacheCleanHelper;
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $productId = intval($this->getRequest()->getParam('id'));
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $storeId = intval($this->getRequest()->getParam('store'));

        $this->pageCacheCleanHelper->cleanPageCache(\Magento\Catalog\Model\Product::ENTITY, [$productId], $storeId);

        $this->messageManager->addSuccessMessage(__('The page cache was successfully cleaned'));

        $resultRedirect = $this->resultRedirectFactory->create();

        $urlParameters = ['id' => $productId];

        if ($productAttributeSetId > 0) {
            $urlParameters['set'] = $productAttributeSetId;
        }

        if ($storeId > 0) {
            $urlParameters['store'] = $storeId;
        }

        $resultRedirect->setPath('catalog/product/edit', $urlParameters);

        return $resultRedirect;
    }
}
