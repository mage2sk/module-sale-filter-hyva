<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Test\Unit\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\Dom;
use Panth\SaleFilter\Block\LayeredNavigation\FilterRenderer as CoreFilterRenderer;
use Panth\SaleFilter\Plugin\LayeredNavigation\FilterRendererPlugin;
use Panth\SaleFilterHyva\Block\LayeredNavigation\FilterRenderer;
use Panth\SaleFilterHyva\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ModuleConfigurationTest extends TestCase
{
    private const MODULE = 'Panth_SaleFilterHyva';

    private string $moduleDir;

    protected function setUp(): void
    {
        $path = (new ComponentRegistrar())->getPath(ComponentRegistrar::MODULE, self::MODULE);
        $this->assertNotNull($path, 'Panth_SaleFilterHyva is not registered');
        $this->moduleDir = realpath($path);
    }

    private function load(string $relative, ?string $schemaUrn = null): \DOMXPath
    {
        $file = $this->moduleDir . '/' . $relative;
        $this->assertFileExists($file);
        $dom = new \DOMDocument();
        $this->assertTrue($dom->load($file), $relative . ' is not well formed');
        if ($schemaUrn !== null) {
            $errors = Dom::validateDomDocument($dom, $schemaUrn);
            $this->assertSame([], array_map('strval', $errors), $relative . ' violates ' . $schemaUrn);
        }

        return new \DOMXPath($dom);
    }

    private function values(\DOMXPath $xpath, string $query): array
    {
        $result = [];
        foreach ($xpath->query($query) as $node) {
            $result[] = trim($node->nodeValue);
        }

        return $result;
    }

    public function testRegistrationPointsToModuleRoot(): void
    {
        $this->assertSame(realpath(dirname(__DIR__, 3)), $this->moduleDir);
    }

    public function testModuleLoadsAfterCoreSaleFilterAndHyva(): void
    {
        $xpath = $this->load('etc/module.xml', 'urn:magento:framework:Module/etc/module.xsd');

        $this->assertSame(
            ['Panth_Core', 'Panth_SaleFilter', 'Hyva_Theme'],
            $this->values($xpath, '//module[@name="Panth_SaleFilterHyva"]/sequence/module/@name')
        );
    }

    public function testComposerRequiresTheBaseModuleAndHyva(): void
    {
        $composer = json_decode((string) file_get_contents($this->moduleDir . '/composer.json'), true);

        $this->assertSame('mage2kishan/module-sale-filter-hyva', $composer['name']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $composer['version']);
        foreach (['mage2kishan/module-core', 'mage2kishan/module-sale-filter', 'hyva-themes/magento2-theme-module'] as $package) {
            $this->assertArrayHasKey($package, $composer['require']);
        }
        $this->assertSame(['Panth\\SaleFilterHyva\\' => ''], $composer['autoload']['psr-4']);
    }

    public function testDefaultConfigExpandsTheFilter(): void
    {
        $xpath = $this->load('etc/config.xml');
        [$section, $group, $field] = explode('/', Config::XML_PATH_DEFAULT_EXPANDED);

        $this->assertSame(['1'], $this->values($xpath, "//default/$section/$group/$field"));
    }

    public function testSystemConfigAddsTheAppearanceGroupToTheBaseSection(): void
    {
        $xpath = $this->load('etc/adminhtml/system.xml');
        $field = '//section[@id="panth_salefilter"]/group[@id="appearance"]/field[@id="default_expanded"]';

        $this->assertSame(1, $xpath->query($field)->length);
        $this->assertSame(['Magento\Config\Model\Config\Source\Yesno'], $this->values($xpath, $field . '/source_model'));
        $this->assertSame(['Expanded By Default'], $this->values($xpath, $field . '/label'));
        $this->assertNotSame([''], $this->values($xpath, $field . '/comment'));
        foreach (['showInDefault', 'showInWebsite', 'showInStore'] as $scope) {
            $this->assertSame(['1'], $this->values($xpath, $field . '/@' . $scope), $scope);
        }
    }

    public static function layoutProvider(): array
    {
        return [
            'category' => ['hyva_catalog_category_view.xml', 'catalog.navigation.renderer'],
            'search' => ['hyva_catalogsearch_result_index.xml', 'catalogsearch.navigation.renderer'],
        ];
    }

    #[DataProvider('layoutProvider')]
    public function testHyvaLayoutPointsThePluginAtTheModuleTemplateAndBlock(string $file, string $renderer): void
    {
        $xpath = $this->load('view/frontend/layout/' . $file);
        $arguments = '//referenceBlock[@name="' . $renderer . '"]/arguments/argument';

        $this->assertSame(
            ['Panth_SaleFilterHyva::layer/filter/sale.phtml'],
            $this->values($xpath, $arguments . '[@name="' . FilterRendererPlugin::DATA_TEMPLATE . '"]')
        );
        $this->assertSame(
            [FilterRenderer::class],
            $this->values($xpath, $arguments . '[@name="' . FilterRendererPlugin::DATA_BLOCK_CLASS . '"]')
        );
        $this->assertTrue(is_a(FilterRenderer::class, CoreFilterRenderer::class, true));
        $this->assertSame(
            [FilterRenderer::class],
            $this->values($xpath, '//referenceContainer[@name="before.body.end"]/block[@name="panth.salefilter.hyva.expand"]/@class')
        );
        $this->assertSame(
            ['Panth_SaleFilterHyva::layer/filter/expand.phtml'],
            $this->values($xpath, '//block[@name="panth.salefilter.hyva.expand"]/@template')
        );
    }

    public function testTemplatesReferencedByLayoutExist(): void
    {
        $this->assertFileExists($this->moduleDir . '/view/frontend/templates/layer/filter/sale.phtml');
        $this->assertFileExists($this->moduleDir . '/view/frontend/templates/layer/filter/expand.phtml');
    }

    public function testLumaLayoutHandlesAreNotTouched(): void
    {
        foreach (['catalog_category_view.xml', 'catalogsearch_result_index.xml', 'default.xml'] as $file) {
            $this->assertFileDoesNotExist($this->moduleDir . '/view/frontend/layout/' . $file);
        }
        $this->assertFileDoesNotExist($this->moduleDir . '/etc/di.xml');
        $this->assertFileDoesNotExist($this->moduleDir . '/etc/frontend/di.xml');
    }
}
