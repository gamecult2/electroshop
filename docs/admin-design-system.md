# Admin UI conventions

The shared admin shell loads `app.css`, `admin.css` and `admin.js`. Bootstrap supplies the grid and components; `admin.css` defines the admin presentation independently of the storefront theme.

## Foundations

- Use `btn-primary` for the main action, `btn-outline-secondary` for secondary actions, and `btn-danger` or `btn-outline-danger` for destructive actions.
- The brand comes from `logo_secondary_color`. `includes/theme.php` validates the hex value, calculates a readable foreground, and provides a darker text-link fallback for pale colors.
- Status colors have separate foreground/background roles. Do not use green as an alternative primary action color.
- Use system fonts, normal body/control text, section headings, and `.admin-meta` or `.x-small` metadata. Do not introduce inline font sizes below 12px.
- Cards use `--app-radius-md`, dialogs use `--app-radius-lg`, and controls use `--app-radius-sm`. Pill shapes are for badges and filters.

## Reuse

- `admin/includes/pages.php`: navigation labels, headings, document titles and active parent routes.
- `admin/includes/ui.php`: card headers, table empty states, status badges and modal footers.
- `admin/includes/product-options.php`: shared specifications and variant fields for both product forms.
- `admin/includes/product-media-upload.php`: shared product upload control.
- `assets/js/product-form.js`: dynamic fields, editor uploads and keyboard image ordering.
- `AdminUI.notify(message, type)`: success/error feedback through the shared live region.
- `AdminUI.confirm(message)`: asynchronous accessible confirmation. Await the returned boolean in a handler; use `data-confirm` on normal links, buttons or forms.
- `AdminUI.busy(control, state)`: pending-request state. Restore controls and prior values after failed saves.
- `AdminUI.storage`: resilient optional tab-state persistence.

Keep mutating business logic in the page/API handlers. Rendering helpers only generate presentation. Buttons that should not submit a form must specify `type="button"`.

## Responsive and accessible patterns

- Wrap tables in `.table-responsive`; operational tables can opt into `.admin-mobile-table` for labelled rows on phones.
- Let toolbars wrap. Avoid fixed-width forms and fixed-height message panels.
- Use actual buttons for actions, labelled fields and meaningful icon-only labels. `AdminUI.refresh` also associates legacy and dynamically inserted controls with labels.
- The mobile sidebar traps focus, restores it on close and makes background content inert. Escape dismisses it.
- Charts share the admin palette, accessible descriptions and an explicit empty state.

## Validation

`tools/check-admin.cjs` renders all 36 admin views through the CLI-only `tools/render-admin-check.php`, then uses an isolated headless Chrome profile at desktop and phone sizes. Set `NODE_PATH` to an installed Playwright package location and run `node tools/check-admin.cjs`. PHP must have PDO MySQL available; the checker enables the existing extension for its child processes.

Optional page arguments limit a rerun, e.g. `node tools/check-admin.cjs products.php orders.php`. Set `ADMIN_SCREENSHOT_DIR` to a temporary directory to capture representative screenshots. The checker performs GET rendering, cancels confirmations and mocks quick-edit failures; it never submits real admin forms. It uses existing local records for detail views and reports missing records as skipped.

Checks cover PHP rendering, JavaScript errors, horizontal overflow, landmarks, accessible control names, mobile navigation, confirmation cancellation, dynamic variants and failed-save recovery. This is not a complete assistive-technology audit or an end-to-end payment/upload test. Vendor PHP deprecation notices are excluded from the UI error count.

Diagnostic, setup and migration utilities retain their existing paths for compatibility but now return 404 over HTTP and run only through PHP CLI. No database migration is needed for these UI changes.
