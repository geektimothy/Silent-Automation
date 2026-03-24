# Silent Automation WordPress Plugin V2

A production-ready WordPress plugin that tracks user behavior and suggests simple automations.

## New in V2
- **WooCommerce Integration**: Automatically tracks "Add to Cart" and "Checkout" events.
- **Advanced Pattern Detection**: Detects cart abandonment and high-value visitors.
- **Automation Builder**: Create custom rules with specific conditions and actions.
- **WhatsApp Triggers**: Send users directly to WhatsApp with pre-filled messages.
- **Tabbed Admin UI**: Improved dashboard with Overview, Automations, and Settings.

## Features
- **Behavior Tracking**: Tracks page visits and time spent using a unique session ID.
- **REST API**: Securely sends tracking data to the WordPress backend.
- **Pattern Detection**: Identifies "High Intent", "Engaged", and "Cart Abandonment".
- **Admin Dashboard**: Native WordPress UI to view stats and suggestions.
- **One-Click Automation**: Activate popups or WhatsApp triggers for specific behavior patterns.

## Installation
1. Download the `silent-automation` folder.
2. Upload the folder to your WordPress installation's `wp-content/plugins/` directory.
3. Log in to your WordPress Admin dashboard.
4. Navigate to **Plugins > Installed Plugins**.
5. Find **Silent Automation** and click **Activate**.

## Usage
- **Overview**: See detected patterns from visitor behavior.
- **Automations**: Build custom rules (e.g., "If Cart Abandonment, show WhatsApp button").
- **Settings**: Configure your WhatsApp number for triggers.

## File Structure
- `silent-automation.php`: Main plugin entry point.
- `includes/`: Core logic (DB, API, Tracking, WooCommerce, Automation, WhatsApp).
- `admin/`: Dashboard UI and AJAX handlers.
- `public/`: Frontend assets and popup logic.
- `assets/`: JavaScript and CSS files.

## Security
- Uses WordPress Nonces for REST API and AJAX requests.
- Sanitizes and validates all inputs.
- Follows WordPress coding standards and OOP principles.
