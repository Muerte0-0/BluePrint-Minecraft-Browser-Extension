# Minecraft Browser Extension for Pterodactyl

This is an open-source **Blueprint extension** for Pterodactyl that adds a **Mods & Plugins** tab right inside your server panel. Browse, search, and install Minecraft mods and plugins from **Modrinth**, or plugins from **SpigotMC**, without leaving the dashboard.

No more downloading files by hand or uploading things manually. Pick what you need, hit install, and you're set.

This is a fork of [fernsehheft/BluePrint-Free-Plugins-Extension](https://github.com/fernsehheft/BluePrint-Free-Plugins-Extension) with mod support added — see [What Changed From Upstream](#what-changed-from-upstream) below.

---

## Features

* 📚 Browse Modrinth **plugins and mods** directly from your Pterodactyl Panel, or SpigotMC plugins via Spiget.
* 🔀 Toggle between **Plugins** and **Mods** — loader filters and the search itself adjust automatically (Paper/Purpur/Spigot/Bukkit/Folia/Velocity/Waterfall/BungeeCord for plugins; Fabric/Forge/NeoForge/Quilt for mods).
* 🔍 Search and filter by project name, Minecraft version, category, and loader.
* ⬇️ One-click download & install — mods are saved to `/mods/`, plugins to `/plugins/`, automatically.
* 🔒 Backend checks server permissions before installing, and whitelists the destination folder server-side.
* 🎨 Modern React-based UI for a smooth experience.
* 🆓 100% free and open source.

**Not yet implemented:** the CurseForge tab visible in the UI is a disabled placeholder — there's no CurseForge API integration behind it. Don't rely on it; SpigotMC and Modrinth (plugins + mods) are the two working sources.

---

## Folder Structure

```
BluePrint-Minecraft-Browser-Extension/
├── conf.yml
├── app/
│   └── MinecraftController.php
├── resources/
│   ├── icon.png
│   ├── views/
│   │   └── view.blade.php
│   └── scripts/
│       └── components/
│           ├── Components.yml
│           └── server/
│               └── minecraft/
│                   └── MinecraftBrowserContainer.tsx
└── routes/
    └── web.php
```

### Quick Overview

* `conf.yml`: Extension metadata & config (identifier, panel wiring)
* `Components.yml`: Registers the panel navigation entry and points it at the React component
* `MinecraftController.php`: Validates and handles mod/plugin downloads, whitelists the save folder
* `MinecraftBrowserContainer.tsx`: UI code (React) — search, filters, Plugins/Mods toggle
* `web.php`: Blueprint route registration

---

## A Note on the Extension Identifier

The Blueprint `identifier` in `conf.yml` is still `modrinthbrowser`, matching the upstream project, even though the repo, files, and UI have been renamed to "Minecraft Browser." This is intentional, not an oversight: Blueprint treats the identifier as the extension's actual internal name — it drives the PHP namespace, the packaged `.blueprint` filename, and Blueprint's install/upgrade bookkeeping. Changing it would make this look like an entirely different extension to Blueprint rather than an update, and would require regenerating scaffolding via Blueprint's own CLI rather than a manual rename. If you're already running the upstream `modrinthbrowser` extension, installing this fork's package will cleanly replace it.

---

## Requirements

* The Pterodactyl Panel with Blueprint support
* Blueprint installed
* PHP 8 or newer
* Outbound network access to:
  * `api.modrinth.com`
  * `cdn.modrinth.com`
  * `api.spiget.org` (for the SpigotMC tab)

---

## Installation (Recommended)

The installation works best with the Blueprint package manager.

### 1️⃣ Download the Latest Release

1. Go to this repo's GitHub Releases page.
2. Download the packaged extension:
    ```
    modrinthbrowser.blueprint
    ```
    (The filename stays `modrinthbrowser.blueprint` regardless of the repo name — see [above](#a-note-on-the-extension-identifier).)

### 2️⃣ Upload to Your Pterodactyl Directory

Put the `modrinthbrowser.blueprint` file in your Pterodactyl root folder:

```
/var/www/pterodactyl
```

You can upload using SFTP, SCP, your file manager, or (less ideally) FTP.

Example:
```
scp modrinthbrowser.blueprint user@server:/var/www/pterodactyl/
```

### 3️⃣ Install the Extension

1. SSH into your server.
2. Run:
    ```
    cd /var/www/pterodactyl
    blueprint -i modrinthbrowser.blueprint
    ```
3. After installing, clear caches and rebuild assets if necessary:
    ```
    php artisan optimize:clear
    php artisan view:clear
    ```
4. If your setup requires it, restart your panel services.

---

## Uninstalling

```
cd /var/www/pterodactyl
blueprint -remove modrinthbrowser.blueprint
php artisan optimize:clear
```

---

## Updating

1. Remove the old version:
    ```
    blueprint -remove modrinthbrowser.blueprint
    ```
2. Download the latest release from GitHub.
3. Upload the new file to `/var/www/pterodactyl`.
4. Install again:
    ```
    blueprint -i modrinthbrowser.blueprint
    ```
5. Clear cache:
    ```
    php artisan optimize:clear
    ```
6. Restart your panel services if needed.

---

## How it Works

### Frontend

```
resources/scripts/components/server/minecraft/MinecraftBrowserContainer.tsx
```
React + Tailwind. Handles the Plugins/Mods toggle, search, filters, and talks to Modrinth's API directly plus this extension's own backend route for downloads.

### Backend

```
app/MinecraftController.php
```
It:
* Validates the request, including a whitelisted `folder` value (`plugins` or `mods` only — never built from a raw client string)
* Verifies your user/server has `file.create` permission
* Streams the file securely via Wings' `DaemonFileRepository`
* Drops it in `/plugins/` or `/mods/` depending on content type

### Security

* Enforces `file.create` permission checks.
* Validates all project/version IDs.
* Whitelists the destination folder server-side (`plugins`|`mods`) — prevents path/directory traversal via a crafted folder value.
* Uses Pterodactyl's built-in storage APIs.

---

## What Changed From Upstream

This fork fixes two bugs in the original that together made mod installation silently impossible:

1. **Search was hardcoded to `project_type:plugin`.** Forge/Fabric/Quilt/NeoForge mods never appeared in results, and the loader filter only ever listed plugin loaders (Paper, Spigot, etc.). Fixed with a Plugins/Mods toggle that adjusts the Modrinth search facets and loader list accordingly.
2. **Downloads always saved to `/plugins/`,** even if bug #1 had been worked around. Mod loaders read from `/mods/`, not `/plugins/`, so a "successfully downloaded" mod would never actually load. Fixed by having the backend accept a whitelisted `folder` parameter tied to the selected content type.

---

## 🛠️ Developing & Local Testing

To run locally, place the extension here:

```
.blueprint/extensions/ModrinthBrowser
```

Then use:

```
blueprint -i modrinthbrowser
```

You'll get hot reloads for most changes.

---

## Contributing

Pull requests, bug reports, and feature ideas are all welcome! Contributions of any size help keep this project healthy.

Typical ways to help:
* Reporting bugs
* Fixing typos or improving docs
* Submitting pull requests
* Suggesting new features

---

## License

Open source, of course! See the `LICENSE` file for specifics.

---

## Acknowledgements

* [fernsehheft](https://github.com/fernsehheft) for the original Modrinth Browser extension this is forked from
* Modrinth API & team
* The Pterodactyl contributors
* Blueprint Framework maintainers
