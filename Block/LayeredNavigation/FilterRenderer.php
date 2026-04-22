<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Block\LayeredNavigation;

use Panth\SaleFilter\Block\LayeredNavigation\FilterRenderer as CoreFilterRenderer;
use Panth\SaleFilter\Model\Config as CoreConfig;
use Panth\SaleFilterHyva\Model\Config as HyvaConfig;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Hyvä-flavoured renderer for the "On Sale" layered-navigation filter.
 *
 * Extends the core cache-aware renderer and exposes Hyvä-specific appearance
 * getters (template style, icon position, animation toggle) so the .phtml
 * template can stay declarative.
 */
class FilterRenderer extends CoreFilterRenderer
{
    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        RequestInterface $request,
        CoreConfig $config,
        LayerResolver $layerResolver,
        private readonly HyvaConfig $hyvaConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $customerSession,
            $request,
            $config,
            $layerResolver,
            $data
        );
    }

    public function getTemplateStyle(): string
    {
        return $this->hyvaConfig->getTemplateStyle();
    }

    public function isDefaultExpanded(): bool
    {
        return $this->hyvaConfig->isDefaultExpanded();
    }

    public function isShowIcon(): bool
    {
        return $this->hyvaConfig->isShowIcon();
    }

    public function getIconPosition(): string
    {
        return $this->hyvaConfig->getIconPosition();
    }

    public function isAnimationEnabled(): bool
    {
        return $this->hyvaConfig->isAnimationEnabled();
    }

    public function getHighlightColor(): string
    {
        return $this->hyvaConfig->getHighlightColor();
    }

    /**
     * Vary cached output per appearance setting so admin changes bust the FPC slot.
     *
     * @return array<int, mixed>
     */
    public function getCacheKeyInfo(): array
    {
        return array_merge(parent::getCacheKeyInfo(), [
            'MAGE2SK_SALEFILTER_HYVA',
            $this->getTemplateStyle(),
            (int) $this->isDefaultExpanded(),
            (int) $this->isShowIcon(),
            $this->getIconPosition(),
            (int) $this->isAnimationEnabled(),
            $this->getHighlightColor(),
        ]);
    }
}
