# WordPress Duplicate Content Remover

A WordPress plugin that helps you identify and manage duplicate content across your website. It detects posts and pages with identical titles or similar URLs (e.g., /post, /post-1, /post-2) and provides tools to manage them efficiently while implementing proper SEO practices.

## Features

- **Duplicate Content Detection**
  - Identifies posts/pages with identical titles
  - Detects URLs with the same base slug (e.g., blog, blog-1, blog-2)
  - Works with both posts and pages
  - Only checks published content

- **SEO Management**
  - Automatically adds canonical URLs to duplicate content
  - Points to the oldest post as the original source
  - Override default WordPress and popular SEO plugin canonical tags
  - Helps prevent duplicate content penalties

- **User-Friendly Interface**
  - Easy-to-use admin interface in WordPress dashboard
  - Clear display of original vs duplicate content
  - Pagination with 20 items per page
  - Bulk delete functionality for duplicates

## Installation

1. Download the plugin ZIP file
2. Go to WordPress admin → Plugins → Add New
3. Click "Upload Plugin" and choose the downloaded ZIP file
4. Click "Install Now"
5. After installation, click "Activate"

## Usage

1. Access via WordPress admin menu → "Duplicate Content"
2. Review duplicate content groups
3. Select duplicates you want to remove
4. Click "Delete Selected Duplicates" to remove them

## SEO Benefits

- Prevents duplicate content penalties from search engines
- Consolidates SEO value to the original post
- Maintains proper link hierarchy
- Improves search engine indexing of your content

## Compatibility

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Compatible with major SEO plugins (Yoast SEO, Rank Math)

## Technical Details

The plugin handles duplicate content in two ways:

1. **URL-based Detection**: Finds posts with URLs like:
   ```
   example.com/post
   example.com/post-1
   example.com/post-2
   ```

2. **Title-based Detection**: Identifies posts with identical titles regardless of URL structure

## Canonical URL Implementation

The plugin automatically:
- Removes default WordPress canonical URLs
- Overrides SEO plugin canonical URLs
- Adds the correct canonical URL pointing to the original content

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.

## Support

For support, please create an issue in the GitHub repository or contact the plugin author.

## Author

Selvakumar Duraipandian
- GitHub: [@selvasudar]

## Changelog

### 1.0.0
- Initial release
- Basic duplicate content detection
- Canonical URL management
- Admin interface with pagination
- Bulk delete functionality