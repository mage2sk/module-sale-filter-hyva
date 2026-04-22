# Panth Sale Filter — Hyvä compatibility

Hyvä-compatible templates (Alpine.js + Tailwind) for the **Panth Sale Filter** layered-navigation filter. Install alongside the core module.

## Installation

```bash
composer require mage2kishan/module-sale-filter-hyva
bin/magento module:enable Panth_SaleFilter Panth_SaleFilterHyva
bin/magento setup:upgrade
bin/magento cache:flush
```

## Requirements

- [`mage2kishan/module-sale-filter`](https://github.com/mage2kishan/module-sale-filter) (core module, hard dep)
- A Hyvä theme active on the storefront

## What this module provides

- A Hyvä-branded render of the "On Sale" filter in layered navigation — Alpine.js expand/collapse, Tailwind styling, no KnockoutJS or RequireJS.
- Zero runtime overlap with Luma: Magento's layout XML merge picks the right template per active theme.

## Support

[Panth Infotech on Upwork](https://www.upwork.com/agencies/1881421506131960778/) | [kishansavaliya.com](https://kishansavaliya.com)
