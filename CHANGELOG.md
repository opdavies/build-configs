# Changelog

## 2023-11-23

### Added

* Test assertions for the expected violation messages.

## 2023-11-22

### Added

* Add CHANGELOG.md.
* Add missing validation rules to the `Configuration` DTO object.
  * Add `dockerfile.stages.*.extra_directories` as an optional list of strings (used in the Drupal Commerce Kickstart example).
  * Allow `php.phpstan` and `php.phpcs` to be `false` or a Collection so their configuration files can not be generated (used in the Drupal Commerce Kickstart example).
  * Add `php.phpunit` and allow it to be set to `false` so its configuration files can not be generated (used in the Drupal Commerce Kickstart example).
    * No further PHPUnit configuration is supported.
  * Add `database.extra_databases`
  * Add `php.phpstan.baseline` as an optional boolean.
  * Add `node.version` as a string.
* Add a test to ensure extra databases is an optional array of non-blank strings.
* Add a test to ensure the project type is a valid type.
* Add a test to ensure the web server is a valid type.
* Add a `test` task to `run` script.

### Changed

* `App\Enum\ProjectType` now returns a string.
* `App\Enum\Webserver` now returns a string.
* `ConfigurationValidatorTest` no longer performs serialisation.
* Use `set -o errexit` and `set -o pipefail` in `run` scripts instead of `set -eu`.
* Use new database credentials by default.
