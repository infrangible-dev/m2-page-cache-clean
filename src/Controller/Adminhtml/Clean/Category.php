<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\PageCacheClean\Controller\Adminhtml\Clean;

use Infrangible\PageCacheClean\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Category
    extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /** @var Data */
    protected $pageCacheCleanHelper;

    public function __construct(
        Context $context,
        Data $pageCacheCleanHelper,
        Date $dateFilter = null,
        StoreManagerInterface $storeManager = null,
        Registry $registry = null,
        Config $wysiwigConfig = null,
        Session $authSession = null
    ) {
        parent::__construct($context, $dateFilter, $storeManager, $registry, $wysiwigConfig, $authSession);

        $this->pageCacheCleanHelper = $pageCacheCleanHelper;
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $categoryId = intval($this->getRequest()->getParam('id'));
        $storeId = intval($this->getRequest()->getParam('store'));

        $this->pageCacheCleanHelper->cleanPageCache(\Magento\Catalog\Model\Category::ENTITY, [$categoryId], $storeId);

        $this->messageManager->addSuccessMessage(__('The page cache was successfully cleaned'));

        $resultRedirect = $this->resultRedirectFactory->create();

        $urlParameters = ['id' => $categoryId];

        if ($storeId > 0) {
            $urlParameters['store'] = $storeId;
        }

        $resultRedirect->setPath('catalog/category/edit', $urlParameters);

        return $resultRedirect;
    }
}