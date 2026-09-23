# SKU and variant repair

Implemented and verified locally on 2026-09-23. Diagnostic pages are preserved.

## Catalog rules

- Product and variant creation/editing are transactional. Variant IDs and SKUs survive edits and reordering; removed variants are retired, not deleted.
- SKU reservations span products and variants, including retired codes. Database triggers enforce cross-table ownership. Locked sequences and bounded transaction retries handle concurrent creation; imported numeric suffixes advance allocation.
- Attribute names are normalized; duplicate combinations, incompatible attribute sets, negative prices, and fractional/negative inventory are rejected. An explicit zero variant price is retained.
- Variant stock is authoritative. Parent stock is the sum of active variants and cannot be edited independently.
- Cart, wishlist-to-cart, reorder and checkout validate actual variants, availability, quantities and discounted prices server-side. Variant products require option selection.
- Checkout revalidates prices and atomically decrements selected inventory. Duplicate submissions do not double-deduct stock. Repeated cancellation does not double-restock.
- Desktop/mobile selectors share option state, SKU, price, stock and quantity. Unavailable combinations cannot be purchased.

## Database migration and recovery

Run `php tools/migrate-sku-variants.php` from the project root when deploying the code to another database. Back up the full database independently and use a maintenance window: MySQL DDL is not transactionally reversible. The migration is rerunnable but takes another snapshot and acquires table locks.

Scoped tables now use InnoDB. Variant references, attribute uniqueness, nonnegative inventory/pricing and checkout identity have database constraints. Redundant exact SKU indexes are removed.

Snapshots are stored outside the document root in the operating-system temporary directory under `electroshop-catalog-backups`. Existing snapshots were moved there intact. These contain private order/customer data: copy them to restricted durable backup storage before OS temporary-file cleanup. Do not publish them or commit them. Restore only through a reviewed database recovery procedure; do not blindly overwrite newer orders.

Original invalid rows are also preserved in `catalog_repair_archive`. The reconciliation archived 249 orphan attribute rows, one cart reference and seven order-item references to missing variants. Orphan attributes were removed from active tables, dangling references were cleared, and the affected cart item was blocked with an explanation. Historical order descriptions and snapshots remain intact; missing variant identities cannot safely be reconstructed.

## Required business-data follow-up

- Product 296, SKU `SMA-032-005`, had price `-658.00`. Its original row was archived; price was normalized to zero and the product deactivated. Enter its correct price before reactivation.
- Historical orders use `inventory_policy=legacy`; new orders use `sku`. Old orders did not reliably deduct variant stock. Their cancellation must not invent variant restocks. Reconcile any historical variant returns manually against actual physical inventory; missing historic option identities are not guessed.

## Verification

- `php tools/test-sku-variants.php`: 29 assertions using connection-local temporary tables; no live catalog changes.
- `php tools/test-sku-concurrency.php`: independent connections verify distinct SKU allocation, exactly one last-unit purchase, and exactly-once cancellation. Creates and drops only its uniquely named disposable test database; requires appropriate database privileges.
- PHP syntax validation across 165 source/tool files passed before the final quantity synchronization adjustment; affected files were rechecked afterward.
- Browser verification on product 421: incomplete options disable purchasing; Aurora Black / 2+32 displays SKU `DIG-057-005-V01`, price 5,500 DA and stock 55. The selected state is preserved at a 390-pixel mobile viewport. No live orders were placed for browser verification.
