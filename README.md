# PHASM WordPress theme

By IamPhasm. WordPress theme for the PHASM website: cybersecurity, learning and IT.

## Install
Appearance › Themes › Add New › Upload Theme → `phasm.zip` → Activate.
Edit content under Appearance › Customize › *PHASM front page* and *PHASM contact & footer*.

## Auto-updates from GitHub
Sites running the theme check `github.com/iamphasm/phasm-theme` for new releases.

1. On each site: Appearance › Themes › PHASM › **Enable auto-updates**.
2. To release a new version:
   - Raise `Version` in `style.css` **and** `PHASM_VERSION` in `functions.php` (same number).
   - Add an entry to `CHANGELOG.md`.
   - Commit, then tag and push:
     ```
     git commit -am "PHASM 1.3.0"
     git tag v1.3.0
     git push && git push --tags
     ```
   - The GitHub Action builds `phasm.zip` and publishes the release. It fails if the tag and the two version numbers don't match.
3. Sites see the update within about 12 hours, or right away via **Check for updates now** on the Themes screen.

Private repo: add `define( 'PHASM_GITHUB_TOKEN', 'github_pat_…' );` to `wp-config.php` on each site (a fine-grained token with read-only *Contents* access to this repo).
Renamed repo: change `PHASM_GITHUB_REPO` in `inc/updater.php`.
