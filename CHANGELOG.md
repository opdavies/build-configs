# Changelog

## Unreleased

### Added

* Add CHANGELOG.md.
* Add missing validation rules to the `Configuration` DTO object.
* Add a test to ensure the project type is a valid type.
* Add a test to ensure the web server is a valid type.

### Changed

* `App\Enum\ProjectType` now returns a string.
* `App\Enum\Webserver` now returns a string.
* `ConfigurationValidatorTest` no longer performs serialisation.
