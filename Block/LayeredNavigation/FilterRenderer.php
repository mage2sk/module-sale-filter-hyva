<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Block\LayeredNavigation;

use Panth\SaleFilter\Block\LayeredNavigation\FilterRenderer as CoreFilterRenderer;
use Panth\SaleFilter\Model\Config as CoreConfig;
use Panth\SaleFilterHyva\Model\Config as HyvaConfig;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class FilterRenderer extends CoreFilterRenderer
{
    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        RequestInterface $request,
        CoreConfig $config,
        LayerResolver $layerResolver,
        private readonly HyvaConfig $hyvaConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $httpContext,
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

    public function getCacheKeyInfo(): array
    {
        return array_merge(parent::getCacheKeyInfo(), [
            'PANTH_SALEFILTER_HYVA',
            (int) $this->isDefaultExpanded(),
        ]);
    }
}
