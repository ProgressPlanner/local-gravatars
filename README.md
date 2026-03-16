# Local Gravatars

[![GitHub release](https://img.shields.io/github/v/release/ProgressPlanner/local-gravatars?label=release)](https://github.com/ProgressPlanner/local-gravatars/releases)
[![License](https://img.shields.io/github/license/ProgressPlanner/local-gravatars)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-plugin-21759b?logo=wordpress&logoColor=white)](https://wordpress.org/plugins/)
[![Privacy](https://img.shields.io/badge/focus-privacy-success)](#why-local-gravatars)

**Serve Gravatar images from your own WordPress site instead of loading them directly from Gravatar.**

Local Gravatars downloads remote Gravatar images, stores them on your site, and rewrites avatar URLs so visitors load them from your own domain. That reduces third-party requests, improves privacy, and can improve performance.

## Why Local Gravatars?

By default, WordPress avatars often load from Gravatar's infrastructure. That means every avatar request can reveal visitor IP addresses and browsing context to a third party.

Local Gravatars helps by:

- serving avatars from your own site URL
- reducing external requests to Gravatar/CDN endpoints
- improving privacy for logged-in users and visitors
- keeping the implementation lightweight and easy to customize
- automatically clearing cached avatar files on a schedule so images can refresh over time

## How it works

1. WordPress generates an avatar URL.
2. Local Gravatars checks whether it is a valid Gravatar URL.
3. The image is downloaded and stored locally in your `wp-content/gravatars` directory.
4. The avatar HTML is updated to use the local file instead of the remote URL.
5. A scheduled cleanup removes cached files regularly so avatars can be refreshed.

## Installation

### From the WordPress admin

1. Go to **Plugins → Add New**.
2. Search for **Local Gravatars**.
3. Install and activate the plugin.

### Manual installation

1. Download this repository as a ZIP.
2. Upload it to `/wp-content/plugins/`.
3. Activate **Local Gravatars** in **Plugins → Installed Plugins**.

## Usage

Activate the plugin and keep using WordPress avatars as usual. No extra configuration is required.

When avatars are requested, the plugin will start caching Gravatar images locally and serve them from your own site.

## Available filters

The plugin includes filters for developers who want to change its behavior:

| Filter | Purpose |
| --- | --- |
| `get_local_gravatars_base_path` | Change where avatar files are stored on disk. |
| `get_local_gravatars_base_url` | Change the public URL used for local avatar files. |
| `get_local_gravatars_cleanup_frequency` | Change how often cached files are removed. |
| `get_local_gravatars_max_process_time` | Change the max processing time before the plugin stops downloading more avatars in the current request. |
| `get_local_gravatars_fallback_url` | Change the fallback image URL when a local copy cannot be used. |
| `local_gravatars_is_valid_url` | Extend or restrict which remote avatar URLs are treated as valid. |

## FAQ

### Does this stop Gravatar from being used completely?

It stops the browser from loading avatar images directly from Gravatar once a local copy is available. If a file has not been cached yet, the plugin may still need to fetch it first.

### Where are avatars stored?

By default, cached avatars are stored in `wp-content/gravatars`.

### Will cached avatars update automatically?

Yes. The plugin schedules cleanup of the local avatar folder so cached files are removed and can be downloaded again later.

### Can I customize the storage location or cleanup schedule?

Yes. The plugin exposes filters for the storage path, public URL, cleanup frequency, max processing time, and fallback image.

## Who is this for?

Local Gravatars is a good fit for:

- privacy-conscious WordPress site owners
- sites trying to reduce third-party requests
- agencies and developers building more privacy-friendly WordPress stacks
- site owners who want a simple, low-maintenance avatar caching solution

## Contributing

Issues and pull requests are welcome. If you plan to change behavior, improve compatibility, or add hooks, please open an issue or PR in this repository.

## License

Local Gravatars is licensed under the [MIT License](LICENSE).
