_default:
  @just --list

compile:
  composer dump-env prod
  ./vendor/bin/box compile
