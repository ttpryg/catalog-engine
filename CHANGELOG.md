# Changelog

All notable changes to `ttpryg/catalog-engine` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-10-18

### Added

- Root `ExceptionInterface` marker interface (`Ttpryg\CatalogEngine\Exceptions\ExceptionInterface`)
  that extends `Throwable` and is implemented by `CatalogEngineException` (and, through
  inheritance, all specific catalog exceptions), so consumers can catch all
  library-specific errors with a single type.
- `.editorconfig` for consistent editor settings across the project.

### Changed

- Added `declare(strict_types=1)` to all source files that were missing it.

## [1.2.0] - 2026-10-16

### Changed

- Enhanced exception handling across the product, variant, and category services.
- Improved repository functionality (`PdoProductRepository`, `PdoVariantRepository`,
  `PdoCategoryRepository`).

## [1.1.1] - 2026-09-21

### Changed

- Added `rector.php` to the `.gitattributes` export-ignore list.

## [1.1.0] - 2026-09-19

### Changed

- Refactored the code structure for improved readability and maintainability.
- Enhanced Docker setup and extended product management features.

## [1.0.0] - 2026-09-17

### Added

- Initial release: comprehensive e-commerce product schema (`Product`, `ProductVariant`,
  `ProductCategory`) with value objects (`Price`, `Dimensions`, `ProductStatus`) and
  sale-price helpers.
- Inventory tracking with `LowStockDetectedEvent` alerts when stock reaches `min_stock`.
- Domain events: `ProductCreatedEvent`, `ProductPriceChangedEvent`, `ProductStockUpdatedEvent`.
- PDO repositories and optional schema with `DatabaseMigrator`.

[Unreleased]: https://github.com/ttpryg/catalog-engine/compare/1.2.0...HEAD
[1.2.0]: https://github.com/ttpryg/catalog-engine/compare/1.1.1...1.2.0
[1.1.1]: https://github.com/ttpryg/catalog-engine/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/ttpryg/catalog-engine/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/ttpryg/catalog-engine/releases/tag/1.0.0
