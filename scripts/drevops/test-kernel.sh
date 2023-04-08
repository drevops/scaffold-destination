#!/usr/bin/env bash
##
# Run tests.
#
# Usage:
# ./test-kernel.sh
#
# shellcheck disable=SC2015

set -e
[ -n "${DREVOPS_DEBUG}" ] && set -x

# Path to the root of the project inside the container.
DREVOPS_APP=/app

# Name of the webroot directory with Drupal installation.
DREVOPS_WEBROOT="${DREVOPS_WEBROOT:-web}"

# Flag to allow Kernel tests to fail.
DREVOPS_TEST_KERNEL_ALLOW_FAILURE="${DREVOPS_TEST_KERNEL_ALLOW_FAILURE:-0}"

# Kernel test group. Optional. Defaults to running Kernel tests tagged with `site:kernel`.
DREVOPS_TEST_KERNEL_GROUP="${DREVOPS_TEST_KERNEL_GROUP:-site:kernel}"

# Kernel test configuration file. Optional. Defaults to core's configuration.
DREVOPS_TEST_KERNEL_CONFIG="${DREVOPS_TEST_KERNEL_CONFIG:-${DREVOPS_APP}/${DREVOPS_WEBROOT}/core/phpunit.xml.dist}"

# Directory to store test result files.
DREVOPS_TEST_REPORTS_DIR="${DREVOPS_TEST_REPORTS_DIR:-}"

# Directory to store test artifact files.
DREVOPS_TEST_ARTIFACT_DIR="${DREVOPS_TEST_ARTIFACT_DIR:-}"

# ------------------------------------------------------------------------------

echo "[INFO] Running Kernel tests"

# Create test reports and artifact directories.
[ -n "${DREVOPS_TEST_REPORTS_DIR}" ] && mkdir -p "${DREVOPS_TEST_REPORTS_DIR}"
[ -n "${DREVOPS_TEST_ARTIFACT_DIR}" ] && mkdir -p "${DREVOPS_TEST_ARTIFACT_DIR}"

opts=(-c "${DREVOPS_TEST_KERNEL_CONFIG}")

[ -n "${DREVOPS_TEST_REPORTS_DIR}" ] && opts+=(--log-junit "${DREVOPS_TEST_REPORTS_DIR}/phpunit/kernel.xml")

vendor/bin/phpunit "${opts[@]}" "${DREVOPS_WEBROOT}/modules/custom/" --exclude-group=skipped --group "${DREVOPS_TEST_KERNEL_GROUP}" "$@" &&
  echo "  [OK] Kernel tests passed." ||
  [ "${DREVOPS_TEST_KERNEL_ALLOW_FAILURE:-0}" -eq 1 ]