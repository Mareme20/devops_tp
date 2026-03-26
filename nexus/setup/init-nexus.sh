#!/bin/sh
set -eu

NEXUS_URL="${NEXUS_URL:-http://nexus:8081}"
NEXUS_USER="${NEXUS_USER:-admin}"
NEXUS_PASSWORD="${NEXUS_PASSWORD:-admin123}"
NEXUS_PASSWORD_FILE="${NEXUS_PASSWORD_FILE:-/nexus-data/admin.password}"
DOCKER_REPO_NAME="${DOCKER_REPO_NAME:-docker-hosted}"
DOCKER_REPO_PORT="${DOCKER_REPO_PORT:-8085}"
NEXUS_WAIT_RETRIES="${NEXUS_WAIT_RETRIES:-60}"
NEXUS_WAIT_SLEEP="${NEXUS_WAIT_SLEEP:-10}"

if [ -f "${NEXUS_PASSWORD_FILE}" ]; then
    NEXUS_PASSWORD="$(tr -d '\r\n' < "${NEXUS_PASSWORD_FILE}")"
fi

echo "Waiting for Nexus to become available..."
i=0
until curl -fsS "${NEXUS_URL}/service/rest/v1/status" >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "${i}" -ge "${NEXUS_WAIT_RETRIES}" ]; then
        echo "Nexus did not become ready in time." >&2
        exit 1
    fi
    sleep "${NEXUS_WAIT_SLEEP}"
done

echo "Checking existing repositories..."
if curl -fsS -u "${NEXUS_USER}:${NEXUS_PASSWORD}" \
    "${NEXUS_URL}/service/rest/v1/repositories" | grep -q "\"name\":\"${DOCKER_REPO_NAME}\""; then
    echo "Repository ${DOCKER_REPO_NAME} already exists."
    exit 0
fi

cat <<EOF >/tmp/docker-hosted.json
{
  "name": "${DOCKER_REPO_NAME}",
  "online": true,
  "storage": {
    "blobStoreName": "default",
    "strictContentTypeValidation": true,
    "writePolicy": "ALLOW"
  },
  "cleanup": {
    "policyNames": []
  },
  "component": {
    "proprietaryComponents": false
  },
  "docker": {
    "v1Enabled": false,
    "forceBasicAuth": true,
    "httpPort": ${DOCKER_REPO_PORT}
  }
}
EOF

echo "Creating Docker hosted repository ${DOCKER_REPO_NAME}..."
curl -fsS -u "${NEXUS_USER}:${NEXUS_PASSWORD}" \
    -H "Content-Type: application/json" \
    -X POST \
    "${NEXUS_URL}/service/rest/v1/repositories/docker/hosted" \
    --data @/tmp/docker-hosted.json

echo "Nexus Docker repository is ready on port ${DOCKER_REPO_PORT}."
