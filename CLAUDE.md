# CLAUDE.md

WooCommerce payment gateway plugin «Allpay Payment Gateway», published in the WordPress.org directory as `allpay-payment-gateway` (https://wordpress.org/plugins/allpay-payment-gateway/, account `allpay`).

## Publishing to WordPress.org

Git (GitHub `ap-extensions/allpay-payment-gateway`) is the only source. Nobody commits to the WordPress.org SVN by hand any more (the old TortoiseSVN checkout next to this repo is obsolete); GitHub Actions do it with the repo secrets `SVN_USERNAME` / `SVN_PASSWORD` (the SVN password from the wp.org profile → Account & Security, not the account password).

- **Release** = push a tag equal to the version (`1.2.1`, a leading `v` is allowed) — `.github/workflows/deploy.yml` replaces SVN `trunk` with the tagged commit, creates `tags/<version>` and syncs `.wordpress-org/` to `assets/`. Before the tag the same commit must have:
  1. `Version:` in `allpay-gateway.php`,
  2. `Stable tag:` in `readme.txt`,
  3. a changelog entry `= <version> – <date> =` at the top of `== Changelog ==` in `readme.txt`.

  The workflow stops with an error if any of them does not match the tag. A pushed release cannot be taken back from users who updated — fix forward with the next version.
- **Readme / page only** (e.g. «Tested up to» bump, description, screenshots) — push to `main` with changes in `readme.txt` or `.wordpress-org/` only: `.github/workflows/readme-assets.yml` updates `trunk/readme.txt`, the readme of the stable tag and `assets/` without a release. A push whose `Stable tag` differs from the one in SVN `trunk/readme.txt` is a release commit and is skipped there (published by the tag), so pushing the release commit and its tag together is safe.
- Commits to `main` that touch code publish nothing until a tag is pushed.

`.wordpress-org/` — directory page assets (icon-128x128 / icon-256x256, screenshot-N matching `== Screenshots ==`), not shipped in the plugin. `.distignore` lists what is left out of the plugin package.

## Translations

`languages/*.po` + `.pot` are the source (edited in Poedit, text domain `allpay-payment-gateway`). `.mo` files are not kept in git (`.gitignore`): the deploy workflow compiles them with `msgfmt`. When adding a user-facing string, wrap it in `__()` / `esc_html__()` with the text domain and add it to the `.pot` and both `.po` (he_IL, ru_RU).
