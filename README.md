# Hmh SpCountDown

Frontend price-box mixin that shows a countdown message when a product has a `special_to_date`. Works with configurable products by resolving the selected child’s special price end date.

## Features
- Adds a notice under the price showing time remaining until `special_to_date`.
- Supports configurable products via swatch selections and `selected_configurable_option`.
- Cleans up when the countdown ends or the price box reloads.

## Installation
- Enable the module: `bin/magento module:enable Hmh_SpCountDown`
- Run setup upgrade/DI compile as needed (or `make deploy` in this repo).

## Usage
No configuration required. The countdown appears automatically when a product has a valid `special_to_date` or child option special end date.
