#!/usr/bin/env bash

set -Eeuo pipefail

trap 'printf "[deploy] ERROR at line %s: %s\n" "$LINENO" "$BASH_COMMAND" >&2' ERR

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
RSYNC_BIN="$(command -v rsync || true)"
PHP_BIN="$(command -v php || true)"
CPANEL_HOME="${HOME:-}"

STAGING_SOURCE="${CPANEL_HOME}/website_khoa"
STAGING_PUBLIC="${CPANEL_HOME}/public_html"
TESTING_SOURCE="${CPANEL_HOME}/website_khoa_test"
TESTING_PUBLIC="${CPANEL_HOME}/test.caothang.site"

fail() {
  printf '[deploy] ERROR: %s\n' "$1" >&2
  exit 1
}

log() {
  printf '[deploy] %s\n' "$1"
}

check_environment() {
  local name="$1"
  local source_root="$2"
  local public_root="$3"
  local media_link="${public_root}/public/media"
  local storage_link="${public_root}/public/storage"

  [[ -d "${source_root}" ]] || fail "${name}: missing source directory ${source_root}"
  [[ -d "${public_root}" ]] || fail "${name}: missing document root ${public_root}"
  [[ -f "${source_root}/.env.staging" ]] || fail "${name}: missing ${source_root}/.env.staging"
  [[ -f "${source_root}/.env.sdk.staging" ]] || fail "${name}: missing ${source_root}/.env.sdk.staging"
  [[ -d "${source_root}/storage/media" ]] || fail "${name}: missing media storage ${source_root}/storage/media"

  if [[ -e "${media_link}" && ! -L "${media_link}" ]]; then
    fail "${name}: ${media_link} is a real file or directory. Move its uploads into ${source_root}/storage/media and remove it before deploying."
  fi

  if [[ -e "${storage_link}" && ! -L "${storage_link}" ]]; then
    fail "${name}: ${storage_link} is a real file or directory. Move its files into ${source_root}/storage and remove it before deploying."
  fi
}

install_bridge() {
  local source_name="$1"
  local public_root="$2"
  local temporary_index="${public_root}/.index.php.deploying"

  printf '%s\n' \
    '<?php' \
    'declare(strict_types=1);' \
    '' \
    "require dirname(__DIR__) . '/${source_name}/index.php';" \
    > "${temporary_index}"
  mv -f -- "${temporary_index}" "${public_root}/index.php"

  cp -- "${SCRIPT_DIR}/public-root.htaccess" "${public_root}/.htaccess.deploying"
  mv -f -- "${public_root}/.htaccess.deploying" "${public_root}/.htaccess"
}

ensure_storage_link() {
  local label="$1"
  local target="$2"
  local link="$3"

  if [[ -L "${link}" ]]; then
    if [[ "$(readlink -f -- "${link}")" == "$(readlink -f -- "${target}")" ]]; then
      log "${label} link already points to ${target}"
      return
    fi
    unlink -- "${link}"
  fi

  ln -s -- "${target}" "${link}"
  log "Linked ${link} -> ${target}"
}

deploy_environment() {
  local name="$1"
  local source_root="$2"
  local public_root="$3"
  local source_name="$4"

  log "${name}: syncing application source"
  "${RSYNC_BIN}" -a --delete \
    --exclude='/.git/' \
    --exclude='/.cpanel.yml' \
    --exclude='/deploy/' \
    --exclude='/.env.*' \
    --exclude='/storage/' \
    --exclude='/public/media/' \
    --exclude='/tests/' \
    --exclude='/tmp/' \
    "${REPOSITORY_ROOT}/" "${source_root}/"

  log "${name}: syncing public assets"
  mkdir -p -- "${public_root}/public"
  "${RSYNC_BIN}" -a --delete --exclude='/media/' --exclude='/storage/' \
    "${REPOSITORY_ROOT}/public/" "${public_root}/public/"

  install_bridge "${source_name}" "${public_root}"
  ensure_storage_link 'Media' "${source_root}/storage/media" "${public_root}/public/media"
  ensure_storage_link 'Storage' "${source_root}/storage" "${public_root}/public/storage"
  "${PHP_BIN}" -l "${source_root}/index.php" >/dev/null
  "${PHP_BIN}" -l "${public_root}/index.php" >/dev/null
  log "${name}: deployment completed"
}

[[ -n "${CPANEL_HOME}" ]] || fail 'HOME is not set'
[[ -n "${RSYNC_BIN}" ]] || fail 'rsync is not available'
[[ -n "${PHP_BIN}" ]] || fail 'php is not available'
[[ -f "${REPOSITORY_ROOT}/index.php" ]] || fail "Repository root is invalid: ${REPOSITORY_ROOT}"
[[ -d "${REPOSITORY_ROOT}/public" ]] || fail 'Repository public directory is missing'
[[ -f "${SCRIPT_DIR}/public-root.htaccess" ]] || fail 'Public .htaccess template is missing'

log 'Running preflight checks for all environments'
check_environment 'staging' "${STAGING_SOURCE}" "${STAGING_PUBLIC}"
check_environment 'testing' "${TESTING_SOURCE}" "${TESTING_PUBLIC}"

deploy_environment 'staging' "${STAGING_SOURCE}" "${STAGING_PUBLIC}" 'website_khoa'
deploy_environment 'testing' "${TESTING_SOURCE}" "${TESTING_PUBLIC}" 'website_khoa_test'

log 'All environments deployed successfully. Migrations were not run.'
