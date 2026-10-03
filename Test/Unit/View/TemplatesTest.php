<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Test\Unit\View;

use Hyva\Theme\ViewModel\HyvaCsp;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Panth\SaleFilterHyva\Block\LayeredNavigation\FilterRenderer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TemplatesTest extends TestCase
{
    private const TEMPLATE_DIR = __DIR__ . '/../../../view/frontend/templates/layer/filter/';

    private function escaper(): Escaper
    {
        $escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnCallback($escape);
        $escaper->method('escapeHtmlAttr')->willReturnCallback($escape);
        $escaper->method('escapeUrl')->willReturnCallback($escape);

        return $escaper;
    }

    private function render(string $template, FilterRenderer $block, ?HyvaCsp $hyvaCsp = null): string
    {
        $escaper = $this->escaper();
        $hyvaCsp = $hyvaCsp ?? $this->createMock(HyvaCsp::class);
        $renderer = static function (string $file) use ($block, $escaper, $hyvaCsp): string {
            ob_start();
            include $file;
            return (string) ob_get_clean();
        };

        return $renderer(self::TEMPLATE_DIR . $template);
    }

    private function saleBlock(?array $items, bool $showCount = true, string $title = 'Sale Status'): FilterRenderer
    {
        $filter = null;
        if ($items !== null) {
            $filter = $this->createMock(FilterInterface::class);
            $filter->method('getItems')->willReturn($items);
        }
        $block = $this->getMockBuilder(FilterRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFilter', 'isShowCount', 'getFilterLabel'])
            ->getMock();
        $block->method('getFilter')->willReturn($filter);
        $block->method('isShowCount')->willReturn($showCount);
        $block->method('getFilterLabel')->willReturn('Configured Label');
        $block->setData('filter_title', $title);

        return $block;
    }

    private function item(string $label, int $count, string $url): DataObject
    {
        return new DataObject(['label' => $label, 'count' => $count, 'url' => $url]);
    }

    public function testSaleTemplateRendersNothingWithoutAFilterOrItems(): void
    {
        $this->assertSame('', trim($this->render('sale.phtml', $this->saleBlock(null))));
        $this->assertSame('', trim($this->render('sale.phtml', $this->saleBlock([]))));
    }

    public function testSaleTemplateRendersAccessibleLinksWithCounts(): void
    {
        $html = $this->render('sale.phtml', $this->saleBlock([
            $this->item('Yes', 5, 'https://example.com/sale.html?sale_filter=1'),
            $this->item('No', 1, 'https://example.com/sale.html?sale_filter=0'),
        ]));

        $this->assertStringContainsString('class="items panth-salefilter-hyva"', $html);
        $this->assertStringContainsString('role="list"', $html);
        $this->assertStringContainsString('aria-label="Sale Status filter options"', $html);
        $this->assertSame(2, substr_count($html, 'rel="nofollow"'));
        $this->assertStringContainsString('href="https://example.com/sale.html?sale_filter=1"', $html);
        $this->assertStringContainsString('aria-label="Yes filter, 5 available products"', $html);
        $this->assertStringContainsString('aria-label="No filter, 1 available product"', $html);
        $this->assertStringContainsString('<span class="count">(5)</span>', $html);
        $this->assertStringContainsString('<span class="count">(1)</span>', $html);
    }

    public function testSaleOptionsMeetTheTapTargetHeight(): void
    {
        $html = $this->render('sale.phtml', $this->saleBlock([
            $this->item('Yes', 2, 'https://example.com/a'),
            $this->item('No', 0, 'https://example.com/b'),
        ]));

        $this->assertSame(2, substr_count($html, 'flex justify-between items-center py-2.5'));
        $this->assertStringNotContainsString('py-1', $html);
    }

    public function testSaleTemplateHidesCountsWhenDisabled(): void
    {
        $html = $this->render('sale.phtml', $this->saleBlock([$this->item('Yes', 4, 'https://example.com/a')], false));

        $this->assertStringNotContainsString('class="count"', $html);
        $this->assertStringContainsString('<span>Yes</span>', $html);
    }

    public function testZeroCountOptionIsPlainTextNotALink(): void
    {
        $html = $this->render('sale.phtml', $this->saleBlock([$this->item('No', 0, 'https://example.com/b')]));

        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('https://example.com/b', $html);
        $this->assertStringContainsString('<span>No</span>', $html);
    }

    public function testSaleTemplateEscapesLabelsAndFallsBackToTheConfiguredLabel(): void
    {
        $html = $this->render(
            'sale.phtml',
            $this->saleBlock([$this->item('<b>Deal</b>', 3, 'https://example.com/a')], true, '')
        );

        $this->assertStringContainsString('aria-label="Configured Label filter options"', $html);
        $this->assertStringContainsString('&lt;b&gt;Deal&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Deal</b>', $html);
    }

    private function expandBlock(bool $expanded): FilterRenderer
    {
        $block = $this->getMockBuilder(FilterRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isDefaultExpanded'])
            ->getMock();
        $block->method('isDefaultExpanded')->willReturn($expanded);

        return $block;
    }

    public function testExpandScriptIsOmittedWhenCollapsedByDefault(): void
    {
        $csp = $this->createMock(HyvaCsp::class);
        $csp->expects($this->never())->method('registerInlineScript');

        $this->assertSame('', trim($this->render('expand.phtml', $this->expandBlock(false), $csp)));
    }

    public function testExpandScriptOpensOnlyTheSaleFilterCardAndIsCspRegistered(): void
    {
        $csp = $this->createMock(HyvaCsp::class);
        $csp->expects($this->once())->method('registerInlineScript');

        $html = $this->render('expand.phtml', $this->expandBlock(true), $csp);

        $this->assertStringStartsWith('<script>', trim($html));
        $this->assertStringEndsWith('</script>', trim($html));
        $this->assertStringContainsString("querySelector('.panth-salefilter-hyva')", $html);
        $this->assertStringContainsString("querySelectorAll('template[x-if]')", $html);
        $this->assertStringContainsString("hasAttribute('x-data')", $html);
        $this->assertStringContainsString("replace(/open:\\s*false/, 'open: true')", $html);
    }
}
