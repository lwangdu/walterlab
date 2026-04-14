# Walter Lab Theme

Custom WordPress child theme for the Walter Lab website, built on top of [GeneratePress](https://generatepress.com/).

## Overview

This repository contains the `walterlab` child theme only.

It includes:

- child-theme styles and theme functions
- custom templates for publications and lab members
- accessibility improvements validated on the live local site

## Requirements

- WordPress
- GeneratePress parent theme
- Advanced Custom Fields Pro
- Lab Member CPT plugin
- Publication CPT plugin

## Theme Location

Place this theme in:

```text
wp-content/themes/walterlab
```

The parent theme should be installed at:

```text
wp-content/themes/generatepress
```

## Main Files

- `style.css` - child theme styles
- `functions.php` - theme hooks, accessibility fixes, footer customization
- `publication_list.php` - publications archive-style page template
- `single-wl_publication.php` - single publication template
- `lab_members.php` - lab members listing template
- `single-wl_member.php` - single lab member template

## Accessibility Work

This theme includes accessibility improvements such as:

- labeled publication search field
- live-region feedback for publication filtering
- improved heading structure on publication pages
- better citation link labels for screen readers
- cleaner keyboard focus behavior for member cards
- removal of problematic inaccessible image lightbox triggers in rendered content

## Notes

- This repo intentionally ignores local backup and working template files.
- The repo tracks the active child theme files only.

## Development

To use this theme locally with Studio:

1. Start the Walter Lab site in WordPress Studio.
2. Make theme changes in this folder.
3. Test pages locally.
4. Commit and push changes to GitHub.

## Repository

GitHub remote:

```text
git@github.com:lwangdu/walterlab.git
```
