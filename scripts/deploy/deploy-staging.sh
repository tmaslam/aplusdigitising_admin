#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_ROOT="${SOURCE_ROOT:-}"

STAGING_DOMAIN="${STAGING_DOMAIN:-staging.aplusdigitizing.com}"
STAGING_URL="${STAGING_URL:-https://${STAGING_DOMAIN}}"
STAGING_SSH_HOST="${STAGING_SSH_HOST:-68.65.121.228}"
STAGING_SSH_USER="${STAGING_SSH_USER:-digixjhl}"
STAGING_SSH_PORT="${STAGING_SSH_PORT:-21098}"
STAGING_APP_PATH="${STAGING_APP_PATH:-/home/digixjhl/staging.aplusdigitizing.com}"
STAGING_SSH_PASSWORD=""
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
SKIP_COMPOSER_INSTALL="${SKIP_COMPOSER_INSTALL:-0}"
ENABLE_VIEW_CACHE="${ENABLE_VIEW_CACHE:-0}"
DRY_RUN="${DRY_RUN:-0}"
INCLUDE_IMAGES="${INCLUDE_IMAGES:-}"
HAS_SSHPASS=0

if command -v sshpass >/dev/null 2>&1; then
  HAS_SSHPASS=1
fi

PRESERVE_PATHS=(
  ".env"
  "uploads"
)

EXCLUDES=(
  ".claude/"
  ".git/"
  ".github/"
  ".DS_Store"
  ".env"
  ".env.example"
  ".env.local.example"
  ".env.production-subdomain.example"
  ".env.production.ready"
  ".editorconfig"
  ".gitattributes"
  ".gitignore"
  ".phpunit.result.cache"
  "README.md"
  "docs/"
  "scripts/deploy/"
  "tests/"
  "node_modules/"
  "vendor/"
  "database/"
  "storage/logs/*"
  "storage/pail/"
  "storage/framework/cache/***"
  "storage/framework/sessions/***"
  "storage/framework/testing/***"
  "storage/framework/views/***"
  "storage/framework/legacy-sessions/***"
  "public/hot"
  "public/build/"
  "public/.htaccess.admin-portal.production-safe"
  "phpunit.xml"
)

usage() {
  cat <<EOF
Usage:
  SOURCE_ROOT=/absolute/path/to/app $(basename "$0")

This script packages the app as a zip, uploads it to staging, preserves only:
  - .env
  - uploads

Then it deletes everything else in the target directory and deploys the new build.
EOF
}

if [[ $# -gt 0 ]]; then
  echo "This script is meant to be run without command-line arguments." >&2
  usage >&2
  exit 1
fi

if [[ -z "${STAGING_SSH_HOST}" || -z "${STAGING_SSH_USER}" || -z "${STAGING_APP_PATH}" ]]; then
  echo "Host, username, and remote app path are required." >&2
  exit 1
fi

if [[ -z "${SOURCE_ROOT}" ]]; then
  echo "SOURCE_ROOT is not set." >&2
  echo "Set SOURCE_ROOT to the Laravel app you want to deploy before running this script." >&2
  echo "Example: SOURCE_ROOT=/path/to/unified-platform-phase-2-github $(basename "$0")" >&2
  exit 1
fi

if [[ ! -d "${SOURCE_ROOT}" ]]; then
  echo "Source root does not exist: ${SOURCE_ROOT}" >&2
  exit 1
fi

if [[ ! -f "${SOURCE_ROOT}/artisan" || ! -f "${SOURCE_ROOT}/public/index.php" ]]; then
  echo "Source root does not look like the expected Laravel app: ${SOURCE_ROOT}" >&2
  exit 1
fi

REMOTE_TARGET="${STAGING_SSH_USER}@${STAGING_SSH_HOST}"
PACKAGE_NAME="staging-deploy-$(date +%Y%m%d-%H%M%S).zip"
TMP_ROOT="$(mktemp -d)"
BUILD_ROOT="${TMP_ROOT}/build"
PACKAGE_PATH="${TMP_ROOT}/${PACKAGE_NAME}"
REMOTE_TMP_DIR="${STAGING_APP_PATH}/.deploy-tmp-$$"
REMOTE_PACKAGE_PATH="${REMOTE_TMP_DIR}/${PACKAGE_NAME}"

cleanup() {
  rm -rf "${TMP_ROOT}"
}
trap cleanup EXIT

quote_for_single() {
  printf "%s" "$1" | sed "s/'/'\"'\"'/g"
}

build_preserve_commands() {
  local fn_name="$1"
  local command_text=""
  local preserve_path

  for preserve_path in "${PRESERVE_PATHS[@]}"; do
    command_text+="${fn_name} \"$(quote_for_single "${preserve_path}")\"\n"
  done

  printf "%b" "${command_text}"
}

echo "Preparing staging package"
echo "  Source : ${SOURCE_ROOT}"
echo "  Target : ${REMOTE_TARGET}:${STAGING_APP_PATH}"
echo "  URL    : ${STAGING_URL}"
echo "  Port   : ${STAGING_SSH_PORT}"
echo "  User   : ${STAGING_SSH_USER}"
echo "  Domain : ${STAGING_DOMAIN}"
echo
echo "This deploy will:"
echo "  1. Build a clean zip from the source app"
echo "  2. Upload it to the staging server"
echo "  3. Preserve only .env and uploads inside the staging app folder"
echo "  4. Delete everything else inside ${STAGING_APP_PATH}"
echo "  5. Extract the new build there"
echo "  6. Run composer install --no-dev"
echo "  7. Run the Laravel cache clear commands"
if [[ "${ENABLE_VIEW_CACHE}" == "1" ]]; then
  echo "  8. Rebuild Blade cache"
else
  echo "  8. Leave Blade uncached for safer frequent staging deploys"
fi
echo

read -r -p "Type yes to continue with this staging deployment: " CONFIRM_DEPLOY
if [[ "${CONFIRM_DEPLOY}" != "yes" ]]; then
  echo "Deployment cancelled."
  exit 0
fi

if [[ -z "${INCLUDE_IMAGES}" ]]; then
  read -r -p "Deploy website images too? Type yes to include public/images and public/testimonial/images: " INCLUDE_IMAGES
fi

if [[ "${INCLUDE_IMAGES}" != "yes" ]]; then
  PRESERVE_PATHS+=(
    "public/images"
    "public/testimonial/images"
  )
fi

if [[ "${HAS_SSHPASS}" == "1" ]]; then
  read -r -s -p "Staging SSH password (leave blank to let ssh/scp prompt securely): " STAGING_SSH_PASSWORD
  echo
else
  STAGING_SSH_PASSWORD=""
  echo "sshpass is not installed locally. SSH/SCP will prompt you directly for the server password once." >&2
fi

SSH_SOCKET="${TMP_ROOT}/ssh-control.sock"
SSH_COMMON_OPTS=(
  -o ControlMaster=auto
  -o ControlPersist=300
  -o "ControlPath=${SSH_SOCKET}"
  -o StrictHostKeyChecking=accept-new
)

if [[ -n "${STAGING_SSH_PASSWORD}" ]] && [[ "${HAS_SSHPASS}" == "1" ]]; then
  SSH_CMD=(sshpass -p "${STAGING_SSH_PASSWORD}" ssh -p "${STAGING_SSH_PORT}" "${SSH_COMMON_OPTS[@]}")
  SCP_CMD=(sshpass -p "${STAGING_SSH_PASSWORD}" scp -P "${STAGING_SSH_PORT}" "${SSH_COMMON_OPTS[@]}")
else
  SSH_CMD=(ssh -p "${STAGING_SSH_PORT}" "${SSH_COMMON_OPTS[@]}")
  SCP_CMD=(scp -P "${STAGING_SSH_PORT}" "${SSH_COMMON_OPTS[@]}")
fi

mkdir -p "${BUILD_ROOT}"

RSYNC_CMD=(rsync -a --delete --prune-empty-dirs)
for preserve_path in "${PRESERVE_PATHS[@]}"; do
  RSYNC_CMD+=(--exclude="${preserve_path}" --exclude="${preserve_path}/***")
done
for exclude_path in "${EXCLUDES[@]}"; do
  RSYNC_CMD+=(--exclude="${exclude_path}")
done
if [[ "${INCLUDE_IMAGES}" != "yes" ]]; then
  RSYNC_CMD+=(--exclude="public/images/" --exclude="public/images/***" --exclude="public/testimonial/images/" --exclude="public/testimonial/images/***")
fi

"${RSYNC_CMD[@]}" "${SOURCE_ROOT}/" "${BUILD_ROOT}/"

(
  echo "Creating deployment zip package..."
  cd "${BUILD_ROOT}"
  COPYFILE_DISABLE=1 zip -qry "${PACKAGE_PATH}" .
)

if [[ "${DRY_RUN}" == "1" ]]; then
  echo "Dry-run complete."
  echo "  Package: ${PACKAGE_PATH}"
  echo "No files were transferred or changed on staging."
  exit 0
fi

"${SSH_CMD[@]}" "${REMOTE_TARGET}" "mkdir -p '$(quote_for_single "${REMOTE_TMP_DIR}")' '$(quote_for_single "${STAGING_APP_PATH}")'"
echo "Uploading package to staging..."
"${SCP_CMD[@]}" "${PACKAGE_PATH}" "${REMOTE_TARGET}:${REMOTE_PACKAGE_PATH}"

REMOTE_SCRIPT=$(cat <<EOF
set -Eeuo pipefail

APP_PATH='$(quote_for_single "${STAGING_APP_PATH}")'
TMP_DIR='$(quote_for_single "${REMOTE_TMP_DIR}")'
PACKAGE_PATH='$(quote_for_single "${REMOTE_PACKAGE_PATH}")'
PRESERVE_DIR="\${TMP_DIR}/preserved"
EXTRACT_DIR="\${TMP_DIR}/extract"

if [ -z "\${APP_PATH}" ] || [ "\${APP_PATH}" = "/" ] || [ "\${APP_PATH}" = "/home" ] || [ "\${APP_PATH}" = "/home/" ] || [ "\${APP_PATH}" = "/root" ] || [ "\${APP_PATH}" = "/root/" ]; then
  echo "Refusing to deploy to unsafe APP_PATH: \${APP_PATH}" >&2
  exit 1
fi

cleanup_remote() {
  rm -rf "\${TMP_DIR}"
}
trap cleanup_remote EXIT

mkdir -p "\${PRESERVE_DIR}" "\${EXTRACT_DIR}"

if [ ! -f "\${PACKAGE_PATH}" ]; then
  echo "Uploaded package not found: \${PACKAGE_PATH}" >&2
  exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
  echo "unzip is required on the staging server." >&2
  exit 1
fi

cd "\${APP_PATH}"

preserve_path() {
  local rel_path="\$1"
  if [ -e "\${APP_PATH}/\${rel_path}" ]; then
    mkdir -p "\${PRESERVE_DIR}/\$(dirname "\${rel_path}")"
    mv "\${APP_PATH}/\${rel_path}" "\${PRESERVE_DIR}/\${rel_path}"
  fi
}

$(build_preserve_commands preserve_path)

find "\${APP_PATH}" -mindepth 1 -maxdepth 1 ! -path "\${TMP_DIR}" -exec rm -rf {} +

unzip -q "\${PACKAGE_PATH}" -d "\${EXTRACT_DIR}"
cp -R "\${EXTRACT_DIR}/." "\${APP_PATH}/"

restore_path() {
  local rel_path="\$1"
  if [ -e "\${PRESERVE_DIR}/\${rel_path}" ]; then
    rm -rf "\${APP_PATH}/\${rel_path}"
    mkdir -p "\${APP_PATH}/\$(dirname "\${rel_path}")"
    mv "\${PRESERVE_DIR}/\${rel_path}" "\${APP_PATH}/\${rel_path}"
  fi
}

$(build_preserve_commands restore_path)

cd "\${APP_PATH}"

mkdir -p bootstrap/cache \
  storage \
  storage/app \
  storage/app/public \
  storage/logs \
  storage/framework \
  storage/framework/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/testing \
  storage/framework/legacy-sessions

ensure_writable_tree() {
  local rel_path="\$1"

  if [ -d "\${APP_PATH}/\${rel_path}" ]; then
    find "\${APP_PATH}/\${rel_path}" -type d -exec chmod 775 {} +
    find "\${APP_PATH}/\${rel_path}" -type f -exec chmod 664 {} +
  fi
}

ensure_writable_tree "storage"
ensure_writable_tree "bootstrap/cache"

if [ ! -f .env ] && [ -f .env.staging.example ]; then
  cp .env.staging.example .env
  echo "No existing .env found. Created .env from .env.staging.example."
fi

if [ ! -f .env ]; then
  echo "Missing .env after deployment. Stopping." >&2
  exit 1
fi

if grep -Eiq '^APP_DEBUG=(true|1)\$' .env; then
  echo "APP_DEBUG is enabled in staging .env. Disable debug before deploying." >&2
  exit 1
fi

if ! grep -Eq '^DB_PASSWORD=.+$' .env; then
  echo "DB_PASSWORD is missing in .env. Set the staging database password and rerun the deploy." >&2
  exit 1
fi

if [ "${SKIP_COMPOSER_INSTALL}" != "1" ]; then
  if command -v "${COMPOSER_BIN}" >/dev/null 2>&1 || [ -x "${COMPOSER_BIN}" ]; then
    "${COMPOSER_BIN}" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
  else
    echo "Composer not found on remote host." >&2
    exit 1
  fi
fi

env_value() {
  local key="\$1"
  local raw

  raw=\$(grep -E "^\${key}=" .env | tail -n 1 | cut -d= -f2- || true)
  raw="\${raw%\"}"
  raw="\${raw#\"}"
  raw="\${raw%\'}"
  raw="\${raw#\'}"
  printf '%s' "\${raw}"
}

if command -v mysql >/dev/null 2>&1; then
  DB_HOST_VALUE="\$(env_value DB_HOST)"
  DB_PORT_VALUE="\$(env_value DB_PORT)"
  DB_NAME_VALUE="\$(env_value DB_DATABASE)"
  DB_USER_VALUE="\$(env_value DB_USERNAME)"
  DB_PASSWORD_VALUE="\$(env_value DB_PASSWORD)"

  if [ -n "\${DB_HOST_VALUE}" ] && [ -n "\${DB_PORT_VALUE}" ] && [ -n "\${DB_NAME_VALUE}" ] && [ -n "\${DB_USER_VALUE}" ] && [ -n "\${DB_PASSWORD_VALUE}" ]; then
    SIGNUP_OFFER_COUNT=\$(MYSQL_PWD="\${DB_PASSWORD_VALUE}" mysql \
      --host="\${DB_HOST_VALUE}" \
      --port="\${DB_PORT_VALUE}" \
      --user="\${DB_USER_VALUE}" \
      --database="\${DB_NAME_VALUE}" \
      --batch --skip-column-names \
      -e "SELECT COUNT(*) FROM site_promotions p INNER JOIN sites s ON s.id = p.site_id WHERE s.legacy_key = '1dollar' AND (p.work_type = 'signup' OR p.discount_type = 'signup_offer');" 2>/dev/null || true)

    SIGNUP_OFFER_COUNT="\$(printf '%s' "\${SIGNUP_OFFER_COUNT}" | tr -d '[:space:]')"

    if [ "\${SIGNUP_OFFER_COUNT}" = "0" ] || [ -z "\${SIGNUP_OFFER_COUNT}" ]; then
      echo "Missing signup welcome offer for site legacy key 1dollar." >&2
      echo "Run scripts/deploy-staging-database.sh or insert the signup offer row before testing customer signup." >&2
      exit 1
    fi
  fi
fi

TWOCHECKOUT_SECRET_WORD_VALUE="\$(env_value TWOCHECKOUT_SECRET_WORD)"
TWOCHECKOUT_SIM_ENABLED_VALUE="\$(printf '%s' "\$(env_value TWOCHECKOUT_SIMULATION_ENABLED)" | tr '[:upper:]' '[:lower:]')"
TWOCHECKOUT_SIM_CUSTOMER_ID_VALUE="\$(env_value TWOCHECKOUT_SIMULATION_CUSTOMER_ID)"
TWOCHECKOUT_SIM_CUSTOMER_EMAIL_VALUE="\$(env_value TWOCHECKOUT_SIMULATION_CUSTOMER_EMAIL)"

PAYMENT_PROVIDER_READY=0

if [ -n "\${TWOCHECKOUT_SECRET_WORD_VALUE}" ]; then
  PAYMENT_PROVIDER_READY=1
fi

if [ "\${TWOCHECKOUT_SIM_ENABLED_VALUE}" = "true" ] || [ "\${TWOCHECKOUT_SIM_ENABLED_VALUE}" = "1" ]; then
  if [ -n "\${TWOCHECKOUT_SIM_CUSTOMER_ID_VALUE}" ] || [ -n "\${TWOCHECKOUT_SIM_CUSTOMER_EMAIL_VALUE}" ]; then
    PAYMENT_PROVIDER_READY=1
  fi
fi

if [ "\${PAYMENT_PROVIDER_READY}" != "1" ]; then
  echo "2Checkout is not ready for staging." >&2
  echo "Set TWOCHECKOUT_SECRET_WORD, or a valid 2Checkout simulation customer if you intentionally want simulation, before testing welcome-payment or billing flows." >&2
  exit 1
fi

if ! grep -Eq '^APP_KEY=.+$' .env; then
  echo "APP_KEY is missing. Generating a new application key for staging..."
  "${PHP_BIN}" artisan key:generate --force
fi

"${PHP_BIN}" artisan optimize:clear
"${PHP_BIN}" artisan view:clear
"${PHP_BIN}" artisan route:clear
"${PHP_BIN}" artisan config:clear
"${PHP_BIN}" artisan cache:clear

find storage/framework/views -type f -name '*.php' -delete 2>/dev/null || true
sleep 2

if [ "${ENABLE_VIEW_CACHE}" = "1" ]; then
  "${PHP_BIN}" artisan view:cache
fi
EOF
)

echo "Running remote deployment steps on staging..."
"${SSH_CMD[@]}" "${REMOTE_TARGET}" "${REMOTE_SCRIPT}"

echo "Staging deployment completed successfully."
echo "Staging URL: ${STAGING_URL}"
