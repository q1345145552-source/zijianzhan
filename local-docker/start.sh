#!/usr/bin/env bash
# 启动 WooCommerce 本地测试环境
set -e
cd "$(dirname "$0")"
export DOCKER_CONFIG="$PWD/.docker-config"
docker compose up -d
echo ""
echo "✅ 环境已启动"
echo "   前台: http://localhost:8080/"
echo "   后台: http://localhost:8080/wp-admin/  (admin / admin123456)"
