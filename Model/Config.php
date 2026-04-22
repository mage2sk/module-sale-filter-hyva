<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Store-scoped reader for Panth_SaleFilterHyva appearance settings.
 *
 * Route every admin-config read for the Hyvä compat module through this
 * class so the XML paths live in exactly one place.
 */
class Config
{
    public const XML_PATH_TEMPLATE_STYLE    = 'panth_salefilter/appearance/template_style';
    public const XML_PATH_DEFAULT_EXPANDED  = 'panth_salefilter/appearance/default_expanded';
    public const XML_PATH_SHOW_ICON         = 'panth_salefilter/appearance/show_icon';
    public const XML_PATH_ICON_POSITION     = 'panth_salefilter/appearance/icon_position';
    public const XML_PATH_ANIMATION_ENABLED = 'panth_salefilter/appearance/animation_enabled';
    public const XML_PATH_HIGHLIGHT_COLOR   = 'panth_salefilter/appearance/highlight_color';

    public const STYLE_DEFAULT = 'default';
    public const STYLE_MINIMAL = 'minimal';
    public const STYLE_CARD    = 'card';

    public const ICON_LEFT  = 'left';
    public const ICON_RIGHT = 'right';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param int|null $storeId Null resolves to the current store.
     */
    public function getTemplateStyle(?int $storeId = null): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_TEMPLATE_STYLE,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );

        return in_array($value, [self::STYLE_DEFAULT, self::STYLE_MINIMAL, self::STYLE_CARD], true)
            ? $value
            : self::STYLE_DEFAULT;
    }

    public function isDefaultExpanded(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEFAULT_EXPANDED,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );
    }

    public function isShowIcon(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_ICON,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );
    }

    public function getIconPosition(?int $storeId = null): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_ICON_POSITION,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );

        return $value === self::ICON_LEFT ? self::ICON_LEFT : self::ICON_RIGHT;
    }

    public function isAnimationEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ANIMATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        );
    }

    /**
     * Falls back to "#e02b27" when the admin value is empty.
     */
    public function getHighlightColor(?int $storeId = null): string
    {
        $value = trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_HIGHLIGHT_COLOR,
            ScopeInterface::SCOPE_STORE,
            $this->resolveStoreId($storeId)
        ));

        return $value !== '' ? $value : '#e02b27';
    }

    protected function resolveStoreId(?int $storeId): int
    {
        if ($storeId !== null) {
            return $storeId;
        }

        return (int) $this->storeManager->getStore()->getId();
    }
}
