#!/bin/bash
set -e

# Ensure our Task Manager directory exists
mkdir -p ${TASK_MANAGER_ROOT}

# Get current www-data user UID-GID
CURRENT_UID="$(id -u www-data)"
CURRENT_GID="$(id -g www-data)"

# Change www-data user UID and GID
[[ ! -z "${TASK_MANAGER_UID}" && "${TASK_MANAGER_UID}" != "${CURRENT_UID}" ]] && \
    usermod -u ${TASK_MANAGER_UID} www-data && \
    find ${TASK_MANAGER_ROOT} -user ${CURRENT_UID} -exec chown -h www-data {} +
[[ ! -z "${TASK_MANAGER_GID}" && "${TASK_MANAGER_GID}" != "${CURRENT_GID}" ]] && \
    groupmod -g ${TASK_MANAGER_GID} www-data && \
    find ${TASK_MANAGER_ROOT} -group ${CURRENT_GID} -exec chgrp -h www-data {} +

exec "$@"
