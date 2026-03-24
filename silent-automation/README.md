# Silent Automation WordPress Plugin

A production-ready WordPress plugin that tracks user behavior and suggests simple automations.

## Features
- **Behavior Tracking**: Tracks page visits and time spent using a unique session ID.
- **REST API**: Securely sends tracking data to the WordPress backend.
- **Pattern Detection**: Identifies "High Intent" (revisits) and "Engaged" (time spent) users.
- **Admin Dashboard**: Native WordPress UI to view stats and suggestions.
- **One-Click Automation**: Activate popups for specific behavior patterns.

## Installation
1. Download the `silent-automation` folder from this repository.
2. Upload the folder to your WordPress installation's `wp-content/plugins/` directory.
3. Log in to your WordPress Admin dashboard.
4. Navigate to **Plugins > Installed Plugins**.
5. Find **Silent Automation** and click **Activate**.

## Usage
- Once activated, the plugin will start tracking anonymous visitor data.
- Go to the **Silent Automation** menu in your sidebar to see detected patterns.
- Click **Activate Automation** on any suggestion to enable the frontend popup for that specific page.

## File Structure
- `silent-automation.php`: Main plugin entry point.
- `includes/`: Core logic (DB, API, Tracking).
- `admin/`: Dashboard UI and AJAX handlers.
- `public/`: Frontend assets and popup logic.
- `assets/`: JavaScript and CSS files.

## Security
- Uses WordPress Nonces for REST API and AJAX requests.
- Sanitizes and validates all inputs.
- Follows WordPress coding standards and OOP principles.
