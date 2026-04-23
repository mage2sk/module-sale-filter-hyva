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
 * Exposes only the one Hyvä-specific toggle ("expanded on first paint")
 * to the .phtml template. Cache key is varied on that flag so admin
 * changes bust the block's FPC slot.
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

    public function isDefaultExpanded(): bool
    {
        return $this->hyvaConfig->isDefaultExpanded();
    }

    /**
     * @return array<int, mixed>
     */
    public function getCacheKeyInfo(): array
    {
        return array_merge(parent::getCacheKeyInfo(), [
            'PANTH_SALEFILTER_HYVA',
            (int) $this->isDefaultExpanded(),
        ]);
    }
}
