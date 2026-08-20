# WooCommerce 本地测试环境

这是用 Docker 在 Mac 上跑的一个完整 WordPress + WooCommerce 测试环境，用于在部署到 VPS 之前先本地验证。

## 访问地址

| 项目 | 地址 |
|------|------|
| 店铺前台 | http://localhost:8080/ |
| 后台 | http://localhost:8080/wp-admin/ |
| 管理员账号 | `admin` |
| 管理员密码 | `admin123456` |

## 常用命令（在本目录下执行）

```bash
./start.sh      # 启动环境
./stop.sh       # 停止环境（数据保留）
```

直接使用 docker compose 时，需要带上独立配置（绕过 macOS 钥匙串凭据报错）：

```bash
export DOCKER_CONFIG="$PWD/.docker-config"
docker compose up -d        # 启动
docker compose down         # 停止
docker compose logs -f      # 看日志
docker compose ps           # 看状态
```

## 用 wp-cli 执行命令（例如）

```bash
export DOCKER_CONFIG="$PWD/.docker-config"
docker compose run --rm cli wp plugin list --allow-root
docker compose run --rm cli wp option get woocommerce_currency --allow-root
```

## 彻底清空重来（会删除所有数据）

```bash
docker compose down -v
```

## 目录结构

- `docker-compose.yml` — 服务定义（mariadb + wordpress + wp-cli）
- `.docker-config/` — 独立 Docker 配置（绕过 Keychain 凭据问题）
- `start.sh` / `stop.sh` — 便捷启停脚本
- 数据存在 Docker 命名卷 `wp_data`（网站文件）和 `db_data`（数据库）中
