_default:
  just --list

build:
  just clean

  # Install dependencies.
  composer validate
  composer install --no-dev --prefer-dist --optimize-autoloader
  composer install --prefer-dist --optimize-autoloader --working-dir ./vendor-bin/box

  composer dump-env prod

  ./bin/build-configs cache:clear
  ./bin/build-configs cache:warmup

  # Generate the phar file.
  box compile --config box.json.dist

  rm -f .env.local .env.local.php

  tree dist/

  # TODO: build a Nix derivation and add it to the store.

clean:
  rm -fr dist/* tmp vendor vendor-bin/box/vendor
  touch dist/.keep var/.keep

ci-test:
  nix develop --command composer install
  nix develop --command just run-snapshots
  nix develop --command vendor/bin/phpunit --testdox

test *args:
  phpunit {{ args }}

create-snapshot config:
  #!/usr/bin/env bash
  set -o nounset

  config_file="tests/snapshots/configs/{{ config }}.yaml"
  output_path="tests/snapshots/output/{{ config }}"

  cat "${config_file}"

  rm -fr "${output_path}"

  ./bin/build-configs app:generate --config-file "${config_file}" --output-dir "${output_path}"

  git status "${output_path}"

run-snapshots:
  #!/usr/bin/env bash
  rm -rf .ignored/snapshots
  mkdir -p .ignored/snapshots

  local configs=(
    # TODO: add more configurations for different types and configurations.
    drupal
    drupal-commerce-kickstart
    drupal-localgov
    fractal
  )

  for config in "${configs[@]}"; do
    config_file="tests/snapshots/configs/${config}.yaml"
    input_path="tests/snapshots/output/${config}"
    output_path=".ignored/snapshots/output/${config}"

    cat "${config_file}"

    ./bin/build-configs app:generate --config-file "${config_file}" --output-dir "${output_path}"

    find "${input_path}" -type f -print0 | while IFS= read -r -d '' original_file; do
      generated_file="${output_path}/${original_file#"${input_path}"/}"

      if cmp -s "${original_file}" "${generated_file}"; then
        echo "Files match: ${original_file}"
      else
        # TODO: show the diff for all failed files. This will stop after the first failure.
        echo "Files do not match: ${original_file}"
        diff "${original_file}" "${generated_file}"
        exit 1
      fi
    done
  done
