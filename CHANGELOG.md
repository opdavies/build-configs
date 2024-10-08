# Changelog

## 2023-12-15

### Fixed

* Make `TTY` configurable in `run` files for Drupal projects.

## 2023-11-24

### Changed

* Simplified constraints on properties within the `Config` DTO class.

## 2023-11-23

### Added

* Test assertions for the expected violation messages.

### Fixed

* The `pre-push` Git hook should use `./run test:commit` instead of `just test-commit` since `just` is no longer used.
* Recursively merge `build.defaults.yaml` into the given configuration.
* Set `TTY` in the `pre-push` Git hook so it can run if using Docker.

### Changed

* Replace `set -ueo` in Git hook templates to use the long names and be consistent with `run` scripts.
* Add `isDocker` and `isFlake` to the Configuration DTO and remove duplicate variables within templates.

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
