<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    public const XML_PATH_DEFAULT_EXPANDED = 'panth_salefilter/appearance/default_expanded';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function isDefaultExpanded(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEFAULT_EXPANDED,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );
    }

    protected function resolveStoreId(?int $storeId): int
    {
        if ($storeId !== null) {
            return $storeId;
        }

        return (int) $this->storeManager->getStore()->getId();
    }
}
