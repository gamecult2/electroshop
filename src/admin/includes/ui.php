<?php
/** Shared presentation primitives for the admin area. */
function admin_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_card_header(string $title, string $icon = '', ?int $count = null): void { ?>
    <div class="card-header admin-card-header">
        <h2 class="h5 mb-0"><?php if ($icon): ?><i class="<?= admin_escape($icon) ?> me-2" aria-hidden="true"></i><?php endif; ?><?= admin_escape($title) ?></h2>
        <?php if ($count !== null): ?><span class="badge admin-count"><?= $count ?> total</span><?php endif; ?>
    </div>
<?php }

function admin_empty_row(int $columns, string $message, string $icon = 'fas fa-inbox'): void { ?>
    <tr><td colspan="<?= $columns ?>"><div class="admin-empty-state"><i class="<?= admin_escape($icon) ?>" aria-hidden="true"></i><p><?= admin_escape($message) ?></p></div></td></tr>
<?php }

function admin_modal_footer(string $label = 'Save changes', string $name = ''): void { ?>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"<?php if ($name): ?> name="<?= admin_escape($name) ?>"<?php endif; ?>><?= admin_escape($label) ?></button>
    </div>
<?php }

function admin_status_badge(bool $active, string $on = 'Active', string $off = 'Inactive'): void { ?>
    <span class="badge admin-status admin-status--<?= $active ? 'success' : 'neutral' ?>"><?= admin_escape($active ? $on : $off) ?></span>
<?php }
