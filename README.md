# Elementor Wrapper Link

![Elementor Wrapper Link](assets/cover.png)

Unify your elements. Unlock new possibilities.

Elementor Wrapper Link adds a clickable wrapper (section/column/container) link for Elementor elements. It supports Elementor Dynamic Tags (ACF, JetEngine, Post URL, etc.) and provides optional external/nofollow flags.

## Features

- Clickable wrapper for Section, Column, Container
- Supports dynamic tags (Post URL, ACF, JetEngine and other Elementor dynamic sources)
- Optional "Open in new tab" and "Add nofollow"
- Defensive loading — does not load the frontend script inside the Elementor editor to avoid breaking the editor

## Installation

1. Copy the `elementor-wrapper-link` folder to your `wp-content/plugins/` directory, or install from this repository.
2. Activate the plugin from the WordPress admin Plugins screen.
3. Make sure Elementor plugin is installed and active.

## Usage

1. Edit a page with Elementor.
2. Select a Section / Column / Container and go to the Advanced tab.
3. Find the "Wrapper Link" section and set the Link URL. You can select a static URL or a Dynamic Tag (Post URL, ACF field, etc.).
4. Optionally toggle "Open in new tab" and "Add nofollow".
5. Save and view the page — the wrapper area will be clickable (but inner anchors/inputs/buttons will behave normally).

## Dynamic tags

- The plugin tries to resolve common dynamic-tag return shapes: strings, arrays with `url`/`value`, numeric post IDs, and WP_Post objects. If a dynamic tag does not return a URL as expected, enable WP_DEBUG and check `wp-content/debug.log` for `[ewl]` logs which show what the dynamic tag returned.

## How to add the cover image shown above

1. Save the cover image you provided as `cover.png` (or `cover.jpg`).
2. Upload it to the `assets/` folder in this repository as `assets/cover.png`.
3. GitHub will render the image automatically in this README.

## Developer notes

- The main plugin file is `elementor-wrapper-link.php`.
- Main class file: `includes/class-elementor-wrapper-link.php`.
- Frontend JS: `assets/js/wrapper-link.js`.

## Changelog

- 1.5 — Fixed wrapper links not working after JetSmartFilters AJAX filtering. Rewrote JS to use event delegation and MutationObserver for full compatibility with dynamic/AJAX content (JetSmartFilters, FacetWP, JetEngine listings).
- 1.4 — Version bump and README / asset updates.
- 1.3 — Improved dynamic-tag handling; avoid loading scripts in editor/REST/AJAX contexts; debug logging when WP_DEBUG is enabled.
- 1.2 — Initial refactor from single-file plugin into structured plugin.

## License
This project is released under the MIT License. See `LICENSE` for details.

## Support
Open an issue on GitHub or contact the author.
