#!/usr/bin/env bash

set -Eeuo pipefail

trap 'printf "[migration] ERROR at line %s: %s\n" "$LINENO" "$BASH_COMMAND" >&2' ERR

PHP_BIN="$(command -v php || true)"
CPANEL_HOME="${HOME:-}"
STAGING_SOURCE="${CPANEL_HOME}/website_khoa"
TESTING_SOURCE="${CPANEL_HOME}/website_khoa_test"

fail() {
  printf '[migration] ERROR: %s\n' "$1" >&2
  exit 1
}

check_environment() {
  local name="$1"
  local source_root="$2"

  [[ -d "${source_root}" ]] || fail "${name}: missing source directory ${source_root}"
  [[ -f "${source_root}/ctsdk.php" ]] || fail "${name}: missing ${source_root}/ctsdk.php"
  [[ -f "${source_root}/.env.sdk.staging" ]] || fail "${name}: missing ${source_root}/.env.sdk.staging"
}

run_migration() {
  local name="$1"
  local source_root="$2"

  printf '[migration] %s: running migrations\n' "${name}"
  (
    cd -- "${source_root}"
    "${PHP_BIN}" ctsdk.php migrate --all
  )
  printf '[migration] %s: completed successfully\n' "${name}"
}

[[ -n "${CPANEL_HOME}" ]] || fail 'HOME is not set'
[[ -n "${PHP_BIN}" ]] || fail 'php is not available'

case "${1:-}" in
  staging)
    check_environment 'staging' "${STAGING_SOURCE}"
    run_migration 'staging' "${STAGING_SOURCE}"
    ;;
  testing)
    check_environment 'testing' "${TESTING_SOURCE}"
    run_migration 'testing' "${TESTING_SOURCE}"
    ;;
  all)
    check_environment 'staging' "${STAGING_SOURCE}"
    check_environment 'testing' "${TESTING_SOURCE}"
    run_migration 'staging' "${STAGING_SOURCE}"
    run_migration 'testing' "${TESTING_SOURCE}"
    ;;
  *)
    fail 'Usage: bash deploy/cpanel-migrate.sh staging|testing|all'
    ;;
esac
