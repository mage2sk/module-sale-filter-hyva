<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Panth\SaleFilterHyva\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testPathMatchesTheSharedSaleFilterSection(): void
    {
        $this->assertSame('panth_salefilter/appearance/default_expanded', Config::XML_PATH_DEFAULT_EXPANDED);
    }

    public static function flagProvider(): array
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }

    #[DataProvider('flagProvider')]
    public function testExplicitStoreIdIsPassedThroughWithoutAskingTheStoreManager(bool $flag): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_DEFAULT_EXPANDED, ScopeInterface::SCOPE_STORE, 7)
            ->willReturn($flag);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->never())->method('getStore');

        $this->assertSame($flag, (new Config($scopeConfig, $storeManager))->isDefaultExpanded(7));
    }

    public function testStoreIdZeroIsTreatedAsAnExplicitScope(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_DEFAULT_EXPANDED, ScopeInterface::SCOPE_STORE, 0)
            ->willReturn(true);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->never())->method('getStore');

        $this->assertTrue((new Config($scopeConfig, $storeManager))->isDefaultExpanded(0));
    }

    public function testCurrentStoreIsUsedWhenNoStoreIdIsGiven(): void
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn('3');
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_DEFAULT_EXPANDED, ScopeInterface::SCOPE_STORE, 3)
            ->willReturn(false);

        $this->assertFalse((new Config($scopeConfig, $storeManager))->isDefaultExpanded());
    }
}
