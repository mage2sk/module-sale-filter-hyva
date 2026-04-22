<?php
declare(strict_types=1);

namespace Panth\SaleFilterHyva\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Panth\SaleFilterHyva\Model\Config;

class IconPosition implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::ICON_LEFT,  'label' => __('Left')],
            ['value' => Config::ICON_RIGHT, 'label' => __('Right')],
        ];
    }
}
