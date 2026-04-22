<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Panth\SaleFilterHyva\Model\Config;

class TemplateStyle implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::STYLE_DEFAULT, 'label' => __('Default')],
            ['value' => Config::STYLE_MINIMAL, 'label' => __('Minimal')],
            ['value' => Config::STYLE_CARD,    'label' => __('Card')],
        ];
    }
}
