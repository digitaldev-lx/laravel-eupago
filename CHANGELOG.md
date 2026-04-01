# Changelog

All notable changes to `laravel-eupago` will be documented in this file.

## [3.0.0] - 2026-04-01

### Added
- Laravel 13 support
- `PaymentMethod` backed string enum (`src/Enums/PaymentMethod.php`)
- Laravel Pint for code style enforcement (`pint.json`)
- `composer lint` and `composer lint:check` scripts

### Changed
- **BREAKING**: Minimum PHP version raised to 8.4
- **BREAKING**: Removed `opcodesio/log-viewer` production dependency (install separately if needed)
- **BREAKING**: Event properties are now `readonly`
- **BREAKING**: Unknown payment methods in callbacks now throw `ValueError` instead of silently succeeding
- PHPStan raised from level 5 to level 6
- Constructor promotion with `readonly` in all payment classes and events
- Error handling extracted to `EuPago` base class (removed duplication from 9 classes)
- `CallbackController` refactored with `match` expression and `PaymentMethod` enum
- Callback logging moved from `rules()` to `passedValidation()` in `CallbackRequest`
- Models use typed return types for relationships and scopes
- `RouteServiceProvider` uses class-based routing
- Base `Controller` simplified (removed unused traits)
- `declare(strict_types=1)` in all PHP files
- Orchestra Testbench updated to support v11.0

### Removed
- PHP 8.3 support
- `opcodesio/log-viewer` production dependency
- Redundant `created_at`/`updated_at` casts in models
- Redundant `newFactory()` overrides in models
- Redundant try/catch blocks that only re-threw exceptions
- Duplicated error handling code across 9 payment classes

### Fixed
- Double semicolon in `EuPagoServiceProvider` namespace declaration

## [2.3.0] - 2026-01-16

### Added

#### Payment Methods
- **Credit Card Single Payments**: Full support for credit card payments with 3D Secure
  - Maximum transaction value: €3,999
  - Supports customer email and custom form timeout
  - Callback support with `CC:PT` payment method code
  - New `CreditCard` class, `CreditCardReference` model, and `Creditcardable` trait

- **Credit Card Recurring Payments (Subscriptions)**: MIT (Merchant Initiated Transactions) support
  - Two-step process: Authorization + Recurring Payments
  - `CreditCardRecurrence` class for creating authorizations
  - `CreditCardRecurringPayment` class for executing recurring charges
  - New `CreditCardRecurrenceAuthorization` and `CreditCardRecurringPayment` models
  - `Creditcardrecurrable` trait for subscription management

- **Google Pay**: Digital wallet payment integration
  - Maximum transaction value: €99,999
  - Supports customer data (email, first name, last name, country code)
  - Callback support with `GP:PT` payment method code
  - New `GooglePay` class, `GooglePayReference` model, and `Googlepayable` trait

- **Apple Pay**: Apple digital wallet payment integration
  - Full customer data support (email, first name, last name, country code)
  - Callback support with `AP:PT` payment method code
  - New `ApplePay` class, `ApplePayReference` model, and `Applepayable` trait

- **Payouts Management**: OAuth-based payout consultation
  - `Payout` class for listing payouts by date range
  - `PayoutTransaction` class for listing settlement transactions
  - Bearer Token authentication support
  - Support for all payment methods (MB, MBWAY, CC, GP, AP)

#### Events System
- **Credit Card Events**:
  - `CreditCardReferenceCreated` / `CreditCardReferenceCreationFailed`
  - `CreditCardReferencePaid`
  - `CreditCardRecurrenceAuthorizationCreated` / `CreditCardRecurrenceAuthorizationFailed`
  - `CreditCardRecurrenceAuthorizationAuthorized`
  - `CreditCardRecurringPaymentCreated` / `CreditCardRecurringPaymentFailed`

- **Google Pay Events**:
  - `GooglePayReferenceCreated` / `GooglePayReferenceCreationFailed`
  - `GooglePayReferencePaid`

- **Apple Pay Events**:
  - `ApplePayReferenceCreated` / `ApplePayReferenceCreationFailed`
  - `ApplePayReferencePaid`

- **Core Events**:
  - `MBReferenceCreated` / `MBReferenceCreationFailed`
  - `MBWayReferenceCreated` / `MBWayReferenceCreationFailed`
  - `MBReferencePaid`
  - `MBWayReferencePaid`
  - `MBReferenceExpired` / `MBWayReferenceExpired`
  - `CallbackReceived` (dispatched for all payment callbacks)
  - `InvalidCallbackReceived`

#### Database & Models
- **Migrations**:
  - `create_credit_card_references_table`
  - `create_credit_card_recurrence_authorizations_table`
  - `create_credit_card_recurring_payments_table`
  - `create_google_pay_references_table`
  - `create_apple_pay_references_table`

- **Models**:
  - `CreditCardReference` with `paid()` scope
  - `CreditCardRecurrenceAuthorization` with `authorized()` and `pending()` scopes
  - `CreditCardRecurringPayment` with `paid()` scope and authorization relationship
  - `GooglePayReference` with `paid()` scope
  - `ApplePayReference` with `paid()` scope

- **Factories**:
  - `CreditCardReferenceFactory` with `paid()` state
  - `CreditCardRecurrenceAuthorizationFactory` with `authorized()` and `pending()` states
  - `CreditCardRecurringPaymentFactory` with `paid()` state
  - `GooglePayReferenceFactory` with `paid()` state
  - `ApplePayReferenceFactory` with `paid()` state
  - `MbReferenceFactory` with `paid()` and `expired()` states
  - `MbwayReferenceFactory` with `paid()` state

#### Infrastructure & Quality
- **PHP 8.4 Support**: Full support for PHP 8.4 alongside PHP 8.3
- **Laravel 12 Support**: Compatible with Laravel 12.x while maintaining Laravel 11.x support
- **Pest Testing Framework**: Comprehensive test suite with 165 tests and 691 assertions
- **Larastan Static Analysis**: Level 5 PHPStan/Larastan integration
- **GitHub Actions CI/CD**: Automated testing across PHP 8.3/8.4 and Laravel 11/12 matrix
- **Composer Scripts**:
  - `composer test`: Run Pest test suite
  - `composer test:coverage`: Run tests with 80% minimum coverage requirement
  - `composer analyse`: Run Larastan static analysis

#### Commands
- **Artisan Command**: `eupago:check-expired` to check and dispatch events for expired references
- Supports Laravel 11/12 scheduling via `routes/console.php`
- Backward compatible with Laravel 10 via `app/Console/Kernel.php`

### Changed
- **Callback Controller**: Unified endpoint handling all payment methods (MB, MBWAY, CC, GP, AP)
- **Backward Compatible**: All new features maintain full backward compatibility
- Updated `composer.json` requirements:
  - PHP: `^8.3 || ^8.4` (was `^8.3`)
  - Laravel: `^11.0 || ^12.0` (was `^11.0`)
- Payment creation methods now dispatch events on both success and failure
- Callback controller dispatches `CallbackReceived` event for all incoming callbacks

### Fixed
- Removed debug `dd($data)` statement in `MB::getParams()` (line 191)
- Fixed typo 'refrencia' → 'referencia' in `CallbackRequest` validation rules
- Added missing return statement in `Mbable::createMbReference()` method
- Removed unused `MbCallbackRequest` import in `CallbackController`

### Documentation
- **Comprehensive README Update**:
  - Complete documentation for all 7 payment methods
  - Laravel 11/12 compatibility notes (event listeners, scheduling)
  - All 19 events documented with usage examples
  - Code examples for every payment method
  - Factory usage examples for testing
  - Updated "About DigitalDev" section
- Added **CONTRIBUTING.md** with contribution guidelines
- Created detailed **CHANGELOG.md**

### Testing
- **165 Tests** covering all payment methods and features
- **691 Assertions** ensuring code reliability
- Unit tests for all classes (MB, MBWay, CreditCard, CreditCardRecurrence, CreditCardRecurringPayment, GooglePay, ApplePay, Payout, PayoutTransaction)
- Model tests for all payment reference models
- Trait tests for all polymorphic relationships
- Feature tests for callback handling across all payment methods
- Command tests for expired reference checking

## [2.2.0] - Previous Release

### Added
- PHP 8.3 and Laravel 11 support
- Support to MB and MBWay payment methods

## [2.1.0] - Legacy Release

### Added
- PHP 8.1+ and Laravel 10 support
- Initial implementation of EuPago payment integration
