# 湘泰商城 · 电商平台装修模板包

这套模板包含 Shopee Mall 手机店布局、TikTok 商城风格、Lazada 商城风格、Shopee 的 4 套配色、首页、购物车和后台品牌化，可以复制到任何 WordPress + WooCommerce 站点复用。

## 文件清单与部署位置

| 文件 | 作用 | 部署到（目标站点） |
| ------ | ------ | ------------------- |
| `shopee-style.css` | 前台全部样式：4 套主题色 + 首页横幅/分类/抢购 + 购物车 + 结账按钮 + 销量评分 | `wp-content/mu-plugins/` |
| `shopee-frontend.js` | 通用前端脚本：轮播、倒计时、销量评分注入 | `wp-content/mu-plugins/` |
| `tiktok-style.css` | TikTok 商城深色与霓虹视觉：全店、商品、购物车和结账页 | `wp-content/mu-plugins/` |
| `tiktok-frontend.js` | TikTok 模板的展示文案和商品墙入场效果 | `wp-content/mu-plugins/` |
| `lazada-style.css` | Lazada 商城蓝紫视觉：全店、商品、购物车和结账页 | `wp-content/mu-plugins/` |
| `lazada-frontend.js` | Lazada 模板的促销标签和商品墙入场效果 | `wp-content/mu-plugins/` |
| `shopee-style-loader.php` | 加载器：根据店长选择加载平台 CSS/JS，并输出商品销量评分数据 | `wp-content/mu-plugins/` |
| `shopee-admin-panel.php` | 店铺装修面板 + 平台模板切换 + 一键应用商品模板 | `wp-content/mu-plugins/` |
| `admin-branding.php` | 后台定制：Shopee 橙主题色 + 后台 logo + 移除 WooCommerce 仪表盘小组件 | `wp-content/mu-plugins/` |
| `header.php` | 前台顶部：logo + 搜索栏 + 购物车图标（Twenty Twenty-Five 主题模板） | `wp-content/themes/twentytwentyfive/patterns/header.php`（覆盖同名文件，原文件备份为 header.php.bak） |
| `home-page.html` | Shopee Mall 手机店首页内容，供装修面板一键应用 | `wp-content/mu-plugins/` |

## 部署步骤（在目标 WordPress 站点）

### 1. 上传 mu-plugin 文件

把下面 10 个文件上传到目标站点：

- `shopee-style.css`
- `shopee-frontend.js`
- `tiktok-style.css`
- `tiktok-frontend.js`
- `lazada-style.css`
- `lazada-frontend.js`
- `shopee-style-loader.php`
- `shopee-admin-panel.php`
- `admin-branding.php`
- `home-page.html`

部署目录：

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

1. 后台 → WooCommerce → 店铺装修
2. 找到「商品模板」卡片
3. 点击「应用商品模板」

按钮会自动创建或更新静态首页，并应用泰语店名、泰语副标题、默认 Mall 配色、双列商品流和手机底部导航。

然后在「平台模板」中选择 Shopee、TikTok 或 Lazada。选择 Shopee 时可以继续选择 4 套配色；选择 TikTok 或 Lazada 时使用对应平台的固定外观。

### 4. 检查前置条件

- 主题需为 **Twenty Twenty-Five**（或选择器结构兼容的区块主题）
- 已安装激活 **WooCommerce**
- 商品有图片和价格（销量/评分数据自动读取 `total_sales` 和评论评分）

## 自定义说明

- **换主色调**：改 `shopee-style.css` 顶部的 `:root` / `[data-shopee-theme="..."]` 变量组
- **平台模板**：由店长在后台统一选择，访客不能切换
- **Shopee 的 4 套配色**：仅在后台选择 Shopee 平台模板时显示
- **后台 logo**：`admin-branding.php` 里 `wp_get_attachment_image_url(44, ...)` 的 `44` 是本地附件 ID，部署后改成目标站点的 logo 附件 ID
- **店铺名**：`admin-branding.php` 和加载器里的「湘泰商城」改成你的店名

## 本地文件备份位置

模板包位置：`local-docker/template-pack/`
原始工作文件：`local-docker/`（shopee-*.css/js/php、header.php、home-page.html）
