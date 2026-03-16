=== Local Gravatars ===
Contributors: aristath
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Host Gravatar images locally in WordPress for better privacy, fewer third-party requests, and faster avatar delivery.

== Description ==

Local Gravatars lets you keep using WordPress avatars while serving the image files from your own site instead of directly from Gravatar.

When an avatar is requested, the plugin downloads the Gravatar image, stores it locally, and rewrites the avatar URL so visitors load it from your own domain. That helps reduce third-party requests, improves privacy, and can improve performance.

= Why site owners use Local Gravatars =

* Reduce direct browser requests to Gravatar.
* Improve visitor privacy by serving avatar files from your own domain.
* Lower dependency on external avatar/CDN requests.
* Keep setup simple: activate the plugin and it starts working.
* Refresh cached avatar files automatically with scheduled cleanup.

= Developer-friendly by design =

The plugin is intentionally lightweight, readable, and easy to customize.

Available filters:

* `get_local_gravatars_base_path` — change where avatar files are stored.
* `get_local_gravatars_base_url` — change the public URL used for local avatar files.
* `get_local_gravatars_cleanup_frequency` — change how often cached files are removed.
* `get_local_gravatars_max_process_time` — change how long processing can run during a request.
* `get_local_gravatars_fallback_url` — set a fallback image URL.
* `local_gravatars_is_valid_url` — extend or restrict which remote avatar URLs are accepted.

== Installation ==

1. Install and activate **Local Gravatars**.
2. Keep using WordPress avatars as usual.
3. The plugin will automatically cache Gravatar images locally as they are requested.

== Frequently Asked Questions ==

= Does this disable Gravatar completely? =

It prevents browsers from loading cached avatar images directly from Gravatar. If an avatar has not been cached yet, the plugin may need to fetch it once before serving it locally.

= Where are avatar files stored? =

By default, files are stored in the `wp-content/gravatars` directory.

= Do cached avatars refresh? =

Yes. The plugin schedules regular cleanup so cached avatar files are removed and can be downloaded again later.

= Can I change the storage location or timing? =

Yes. Use the provided filters to change the storage path, public URL, cleanup frequency, processing time, and fallback image behavior.

= Is there any configuration screen? =

No. The plugin is designed to work out of the box with no settings page.

== Changelog ==

= 1.1.3 =
* Improve avatar file handling and extension detection.
* Add stricter validation for remote Gravatar URLs.
* Improve initialization and file lookup performance.
* Keep the codebase lightweight and easier to maintain.

= 1.1.2 =
* Previous release improvements.
