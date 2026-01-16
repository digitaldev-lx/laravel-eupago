# Contributing to Laravel EuPago

First off, thank you for considering contributing to Laravel EuPago! It's people like you that make this package better for everyone.

## Code of Conduct

This project and everyone participating in it is governed by respect and professionalism. By participating, you are expected to uphold this standard.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

* **Use a clear and descriptive title**
* **Describe the exact steps which reproduce the problem**
* **Provide specific examples to demonstrate the steps**
* **Describe the behavior you observed after following the steps**
* **Explain which behavior you expected to see instead and why**
* **Include your PHP version, Laravel version, and package version**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

* **Use a clear and descriptive title**
* **Provide a step-by-step description of the suggested enhancement**
* **Provide specific examples to demonstrate the steps**
* **Describe the current behavior and explain which behavior you expected to see instead**
* **Explain why this enhancement would be useful**

### Pull Requests

1. Fork the repo and create your branch from `master`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code follows the existing code style
6. Issue that pull request!

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/laravel-eupago.git
cd laravel-eupago

# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage (minimum 80% required)
composer test:coverage

# Run static analysis
composer analyse
```

## Development Guidelines

### Code Style

* Follow PSR-12 coding standards
* Use type hints for all parameters and return types
* Write meaningful variable and method names
* Add PHPDoc blocks for classes and public methods

### Testing

* Write Pest PHP tests for all new features
* Maintain minimum 80% code coverage
* Test both success and failure scenarios
* Use factories for model creation in tests
* Mock external API calls (EuPago API)

### Commits

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference issues and pull requests liberally after the first line

### Events

When adding new functionality that warrants event dispatching:

* Create the event class in `src/Events/`
* Follow the naming convention: `{Entity}{Action}` (e.g., `MBReferenceCreated`)
* Include all relevant data as public properties
* Document the event in README.md under the Events section
* Dispatch the event at the appropriate point in the code flow
* Write tests to verify the event is dispatched correctly

### Commands

When creating new Artisan commands:

* Place them in `src/Console/`
* Use clear, descriptive command signatures
* Provide helpful command descriptions
* Register commands in `EuPagoServiceProvider`
* Write feature tests for command functionality
* Document commands in README.md

## Testing Your Changes

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test:coverage

# Run specific test file
vendor/bin/pest tests/Unit/MB/MBTest.php

# Run tests matching a pattern
vendor/bin/pest --filter=MBReference
```

### Static Analysis

```bash
# Run Larastan
composer analyse

# Run with specific level
vendor/bin/phpstan analyse --level=5
```

### Manual Testing

1. Create a test Laravel application
2. Require your local package using a path repository in composer.json:
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "../laravel-eupago"
           }
       ],
       "require": {
           "digitaldev-lx/laravel-eupago": "@dev"
       }
   }
   ```
3. Test your changes in the application context

## Release Process

Releases are managed by package maintainers. The process includes:

1. Update version in CHANGELOG.md
2. Ensure all tests pass on CI/CD
3. Ensure code coverage meets 80% minimum
4. Create a GitHub release with tag
5. Update Packagist automatically via webhook

## Questions?

Feel free to open an issue for any questions or concerns. We're here to help!

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
