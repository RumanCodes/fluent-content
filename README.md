# Fluent Content

**Revolutionize your content management with AI-powered automation**

Fluent Content is a powerful WordPress plugin that combines advanced AI-driven generation, intelligent automation, and seamless integrations to help you plan, create, and distribute high-quality content across your website and social channels with minimal effort.

## 🚀 Features

- **AI-Powered Content Generation**: Create engaging blog posts, product descriptions, and featured images from simple keywords
- **Automated Content Planning**: Schedule an entire month's worth of content automatically
- **Data-Driven Insights**: Leverage intelligent analytics for optimal timing and content strategy
- **Set It and Forget It**: Eliminate time-consuming manual processes with full automation
- **Professional Results**: Deliver high-quality content at a fraction of traditional costs
- **Perfect for**: Small businesses, e-commerce owners, content creators, and digital marketers
_(Currently, blog posts creating is developed)_

## Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **Node.js**: Version 18
- **Composer**: Installed globally
- **NPM**: Installed globally

## Installation & Setup

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Generate Autoload Files
```bash
composer dump-autoload
```

### 3. Install Node Modules
```bash
npm install
```

### 4. Configure Development Environment

#### Update Vite Configuration
- Open `vite.config.js` and configure the `root` and `devServer` URL for your local environment
- Update the dev server URL in `app/Hooks/Handlers/AdminMenuHandlers.php` to match your Vite server (default: `http://localhost:5173`)
- This enables hot module replacement for Vue components without page reloads

### 5. Build Assets

For development with hot reload:
```bash
npm run dev
```

For production build:
```bash
npm run build
```

### 6. Activate the Plugin

1. Upload the `fluent-content` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Fluent Content** in your WordPress admin panel to get started

## 🔧 Development

### File Structure
```
fluent-content/
├── app/                    # PHP application files
│   ├── Hooks/             # WordPress hooks and handlers
│   └── ...
├── resources/                    # Frontend source files (Vue/JS/CSS)
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
└── vite.config.js         # Vite configuration
```

### Development Workflow

1. Run `npm run dev` to start the development server with hot reload
2. Make changes to PHP files in `app/` or frontend files in `src/`
3. Run `composer dump-autoload` after adding new PHP classes
4. Run `npm run build` before deploying to production

## Documentation

For detailed documentation on features, configuration, and usage, visit [Fluent Content Documentation](#).

## Support

Need help? Here's how to get support:

- **Documentation**: Check our comprehensive guides
- **Issues**: Report bugs or request features on GitHub Issues
- **Community**: Join our community forum

## License

This plugin is licensed under the [GPL v2 or later](http://www.gnu.org/licenses/gpl-2.0.html).

## Why Fluent Content?

Stop spending hours on content creation. Fluent Content's "set it and forget it" approach lets you focus on growing your business while AI handles your content strategy. Whether you're managing a small blog or a large e-commerce site, Fluent Content delivers professional results automatically.

---

**Ready to transform your content workflow?** Install Fluent Content today and experience the future of automated content management.

## Video Demo
**Demo**: [Demo Link](#).
