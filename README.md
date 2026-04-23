# Panth Sale Filter — Hyvä compatibility

[![Latest Version](https://img.shields.io/packagist/v/mage2kishan/module-sale-filter-hyva.svg?style=flat-square)](https://packagist.org/packages/mage2kishan/module-sale-filter-hyva)
[![Magento 2.4](https://img.shields.io/badge/Magento-2.4-orange.svg?style=flat-square&logo=magento&logoColor=white)](https://developer.adobe.com/commerce/)
[![PHP 8.1 - 8.4](https://img.shields.io/badge/PHP-8.1%20--%208.4-blue?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Hyvä 1.3+](https://img.shields.io/badge/Hyv%C3%A4-1.3+-14b8a6?style=flat-square)](https://hyva.io/)
[![License](https://img.shields.io/badge/license-Proprietary-lightgrey.svg?style=flat-square)](LICENSE)

Hyvä-native storefront template for the [**Panth Sale Filter**](https://packagist.org/packages/mage2kishan/module-sale-filter) — Alpine.js + Tailwind, zero jQuery. Ships the *Appearance* admin group so you can toggle the collapsed/expanded default from Stores → Configuration.

> **Requires** [`mage2kishan/module-sale-filter`](https://packagist.org/packages/mage2kishan/module-sale-filter) (the core module that owns the indexer, the filter logic, and the Luma template). Installing this package alone does nothing.

---

## Screenshots

### Storefront sidebar

![Hyvä storefront sidebar](docs/images/hyva-sidebar.png)

### Admin — configuration

![Admin configuration (Appearance section, Hyvä group)](docs/images/admin-configuration.png)

### Admin — live demo

![Admin configuration demo](docs/images/admin-config-demo.gif)

### Indexer registration (inherited from the core module)

![Index Management](docs/images/admin-index-management.png)

---

## What this module does

- Registers the Hyvä-native layered-navigation template at `Panth_SaleFilter::layer/filter/sale.phtml`, replacing the core module's Luma Knockout markup when a Hyvä theme is active. Alpine.js handles the open/close interaction; Tailwind handles the styling — no jQuery, no requireJS.
- Adds the **Appearance (Hyvä)** admin group at *Stores → Configuration → Panth Extensions → Sale Filter* with one toggle:
  - **Expanded By Default** — whether the filter renders open or collapsed on first paint (default: *Yes*).
- Varies the filter renderer's FPC cache key on that toggle so admin changes bust the cached block slot.

Everything else (indexer, admin grid, filter logic, URL params, label config, discount-source switches, etc.) lives in the core module — see its [README](https://github.com/mage2sk/module-sale-filter#readme) for the full story.

---

## Compatibility

| Component | Version |
|---|---|
| Magento Open Source / Adobe Commerce | **2.4.x** (tested 2.4.4 – 2.4.8) |
| PHP | 8.1 · 8.2 · 8.3 · 8.4 |
| Hyvä Themes | **1.3+** |
| Core module | [`mage2kishan/module-sale-filter`](https://packagist.org/packages/mage2kishan/module-sale-filter) **^1.0.4** |

---

## Installation

```bash
# This pulls the core module automatically (hard dependency).
composer require mage2kishan/module-sale-filter-hyva

bin/magento module:enable Panth_SaleFilter Panth_SaleFilterHyva
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento indexer:reindex panth_salefilter_product
bin/magento cache:flush
```

---

## Configuration

**Stores → Configuration → Panth Extensions → Sale Filter → Appearance (Hyvä)**

| Field | Notes |
|---|---|
| Expanded By Default | Renders the filter in its open state on first paint. Off = collapsed until the shopper clicks the header. Store-scoped. |

All other settings (enable/disable, labels, counts, position, discount sources) live in the *General* group and belong to the core module.

---

## Template override

The filter renders via:

```
app/design/frontend/<YourVendor>/<YourHyvaChild>/Panth_SaleFilter/templates/layer/filter/sale.phtml
```

Copy [`view/frontend/templates/layer/filter/sale.phtml`](view/frontend/templates/layer/filter/sale.phtml) into your theme to customise markup without forking the module.

---

## Uninstall

```bash
bin/magento module:disable Panth_SaleFilterHyva
composer remove mage2kishan/module-sale-filter-hyva
bin/magento setup:upgrade
```

Removing only this module reverts Hyvä storefronts to the core module's Luma template (works but visually off-theme). Remove both to drop the filter entirely.

---

## Changelog

### 1.0.1
- Pin hard dependency on `mage2kishan/module-sale-filter` to `^1.0.4` so installers always pull the sort + realtime fixes.
- README rewrite with screenshots and live-demo GIF.

### 1.0.0
- Initial release.

---

## Support

- Issues, feature requests: <kishansavaliyakb@gmail.com>
- Author: [Kishan Savaliya](https://kishansavaliya.com)

---

## License

Proprietary. See [LICENSE](LICENSE).
