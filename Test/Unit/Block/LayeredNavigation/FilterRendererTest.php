<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Test\Unit\Block\LayeredNavigation;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template\File\Resolver as TemplateResolver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Panth\SaleFilter\Block\LayeredNavigation\FilterRenderer as CoreFilterRenderer;
use Panth\SaleFilter\Model\Config as CoreConfig;
use Panth\SaleFilterHyva\Block\LayeredNavigation\FilterRenderer;
use Panth\SaleFilterHyva\Model\Config as HyvaConfig;
use PHPUnit\Framework\TestCase;

class FilterRendererTest extends TestCase
{
    private function block(
        ?HyvaConfig $hyvaConfig = null,
        ?CoreConfig $coreConfig = null,
        ?StoreManagerInterface $storeManager = null,
        ?Resolver $resolver = null,
        array $data = []
    ): FilterRenderer {
        $storeManager = $storeManager ?? $this->createStub(StoreManagerInterface::class);
        $context = $this->createStub(Context::class);
        $context->method('getStoreManager')->willReturn($storeManager);
        $context->method('getResolver')->willReturn($this->createStub(TemplateResolver::class));
        $context->method('getUrlBuilder')->willReturn($this->createStub(UrlInterface::class));
        $context->method('getAppState')->willReturn($this->createStub(State::class));

        return new FilterRenderer(
            $context,
            $storeManager,
            $this->createStub(HttpContext::class),
            $this->createStub(RequestInterface::class),
            $coreConfig ?? $this->createStub(CoreConfig::class),
            $resolver ?? $this->createStub(Resolver::class),
            $hyvaConfig ?? $this->createStub(HyvaConfig::class),
            $data
        );
    }

    private function hyvaConfig(bool $expanded): HyvaConfig
    {
        $config = $this->createStub(HyvaConfig::class);
        $config->method('isDefaultExpanded')->willReturn($expanded);

        return $config;
    }

    public function testExtendsTheBaseRendererSoThePluginAcceptsIt(): void
    {
        $this->assertInstanceOf(CoreFilterRenderer::class, $this->block());
        $this->assertTrue(is_a(FilterRenderer::class, CoreFilterRenderer::class, true));
    }

    public function testDefaultExpandedComesFromTheHyvaConfig(): void
    {
        $this->assertTrue($this->block($this->hyvaConfig(true))->isDefaultExpanded());
        $this->assertFalse($this->block($this->hyvaConfig(false))->isDefaultExpanded());
    }

    public function testInheritedFilterLabelCountAndFilterAccessorsStillWork(): void
    {
        $core = $this->createStub(CoreConfig::class);
        $core->method('getFilterLabel')->willReturn('Sale Status');
        $core->method('isShowCount')->willReturn(true);
        $filter = $this->createStub(FilterInterface::class);
        $block = $this->block(null, $core, null, null, ['filter' => $filter]);

        $this->assertSame('Sale Status', $block->getFilterLabel());
        $this->assertTrue($block->isShowCount());
        $this->assertSame($filter, $block->getFilter());
        $this->assertSame([CoreConfig::CACHE_TAG], $block->getIdentities());
    }

    private function cacheKey(bool $expanded): array
    {
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getWebsiteId')->willReturn(1);
        $store->method('getCode')->willReturn('default');
        $store->method('getCurrentCurrencyCode')->willReturn('USD');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn(47);
        $layer = $this->createStub(Layer::class);
        $layer->method('getCurrentCategory')->willReturn($category);
        $resolver = $this->createStub(Resolver::class);
        $resolver->method('get')->willReturn($layer);

        return $this->block($this->hyvaConfig($expanded), null, $storeManager, $resolver)->getCacheKeyInfo();
    }

    public function testCacheKeyExtendsTheBaseKeyWithTheExpandedState(): void
    {
        $expanded = $this->cacheKey(true);
        $collapsed = $this->cacheKey(false);

        $this->assertContains('MAGE2SK_SALEFILTER', $expanded);
        $this->assertContains(47, $expanded);
        $this->assertSame(['PANTH_SALEFILTER_HYVA', 1], array_slice(array_values($expanded), -2));
        $this->assertSame(['PANTH_SALEFILTER_HYVA', 0], array_slice(array_values($collapsed), -2));
        $this->assertNotSame($expanded, $collapsed);
    }
}
