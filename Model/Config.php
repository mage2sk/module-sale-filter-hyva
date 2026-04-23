<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Store-scoped reader for the single Hyvä appearance setting.
 *
 * Heavier look-and-feel options were removed so the template stays lean
 * and uses Hyvä's default Tailwind conventions. Only the "open or
 * collapsed on first paint" toggle is exposed.
 */
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
