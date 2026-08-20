# 湘泰商城 · Shopee 装修模板包

这套模板包含你在本地测试环境里做好的全部装修（Shopee 橙风格 + 4 套主题 + 首页 + 购物车 + 后台品牌化），可以复制到任何 WordPress + WooCommerce 站点直接复用。

## 文件清单与部署位置

| 文件 | 作用 | 部署到（目标站点） |
| ------ | ------ | ------------------- |
| `shopee-style.css` | 前台全部样式：4 套主题色 + 首页横幅/分类/抢购 + 购物车 + 结账按钮 + 销量评分 | `wp-content/mu-plugins/` |
| `shopee-frontend.js` | 前端脚本：轮播、倒计时、销量评分注入、主题切换器 | `wp-content/mu-plugins/` |
| `shopee-style-loader.php` | 加载器：输出 CSS/JS + 商品销量评分数据 + 主题切换按钮 | `wp-content/mu-plugins/` |
| `admin-branding.php` | 后台定制：Shopee 橙主题色 + 后台 logo + 移除 WooCommerce 仪表盘小组件 | `wp-content/mu-plugins/` |
| `header.php` | 前台顶部：logo + 搜索栏 + 购物车图标（Twenty Twenty-Five 主题模板） | `wp-content/themes/twentytwentyfive/patterns/header.php`（覆盖同名文件，原文件备份为 header.php.bak） |
| `home-page.html` | 首页内容（横幅轮播 + 分类导航 + 限时抢购 + 精选商品），Gutenberg 区块格式 | 后台新建「首页」页面 → 用代码编辑器粘贴全文 → 设为静态首页 |

## 部署步骤（在目标 WordPress 站点）

### 1. 上传 mu-plugin 文件

把 `shopee-style.css`、`shopee-frontend.js`、`shopee-style-loader.php`、`admin-branding.php` 4 个文件上传到：

```text
wp-content/mu-plugins/
```

mu-plugin 会自动加载，无需在后台启用。

### 2. 替换顶部模板（可选）

覆盖主题的 header 模板：

```text
wp-content/themes/twentytwentyfive/patterns/header.php
```

> 注意：`header.php` 里硬编码了 logo 图片地址 `http://localhost:8080/.../4.png`，部署后需要换成目标站点的 logo 地址。

### 3. 设置首页

1. 后台 → 页面 → 新建页面 → 标题「首页」
2. 右上角「选项」→ 开启「代码编辑器」
3. 粘贴 `home-page.html` 全部内容 → 发布
4. 设置 → 阅读 → 静态首页 → 选择「首页」

### 4. 检查前置条件

- 主题需为 **Twenty Twenty-Five**（或选择器结构兼容的区块主题）
- 已安装激活 **WooCommerce**
- 商品有图片和价格（销量/评分数据自动读取 `total_sales` 和评论评分）

## 自定义说明

- **换主色调**：改 `shopee-style.css` 顶部的 `:root` / `[data-shopee-theme="..."]` 变量组
- **4 套主题切换**：前台右下角 🎨 按钮，选择保存在 localStorage
- **后台 logo**：`admin-branding.php` 里 `wp_get_attachment_image_url(44, ...)` 的 `44` 是本地附件 ID，部署后改成目标站点的 logo 附件 ID
- **店铺名**：`admin-branding.php` 和加载器里的「湘泰商城」改成你的店名

## 本地文件备份位置

模板包位置：`local-docker/template-pack/`
原始工作文件：`local-docker/`（shopee-*.css/js/php、header.php、home-page.html）
