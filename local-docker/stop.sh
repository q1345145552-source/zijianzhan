#!/usr/bin/env bash
# 停止 WooCommerce 本地测试环境（保留数据）
set -e
cd "$(dirname "$0")"
export DOCKER_CONFIG="$PWD/.docker-config"
docker compose down
echo "✅ 已停止（数据已保留，下次运行 ./start.sh 恢复）"
