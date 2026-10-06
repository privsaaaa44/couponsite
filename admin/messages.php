<?php
// admin/messages.php - Contact Form Inquiries Management
include __DIR__ . '/includes/admin_header.php';

// Handle delete / mark read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = "Security token mismatch.";
        header('Location: messages.php');
        exit;
    }

    $post_action = $_POST['post_action'] ?? '';
    $msg_id = (int)($_POST['msg_id'] ?? 0);

    if ($post_action === 'delete' && $msg_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->execute([':id' => $msg_id]);
        $_SESSION['flash_success'] = "Message deleted successfully.";
    } elseif ($post_action === 'mark_read' && $msg_id > 0) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
        $stmt->execute([':id' => $msg_id]);
        $_SESSION['flash_success'] = "Message marked as read.";
    }

    header('Location: messages.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC");
$messages = $stmt->fetchAll();
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Contact Messages & Inquiries</h1>
        <p class="text-muted small mb-0">View messages submitted via the public contact form.</p>
    </div>
</div>

<div class="bg-white border rounded-3 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td class="small text-muted" style="white-space: nowrap;">
                                <?= date('M j, Y H:i', strtotime($m['created_at'])) ?>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= e($m['name']) ?></strong>
                                <a href="mailto:<?= e($m['email']) ?>" class="small text-muted"><?= e($m['email']) ?></a>
                            </td>
                            <td>
                                <strong class="small text-primary"><?= e($m['subject']) ?></strong>
                            </td>
                            <td class="small text-muted" style="max-width: 350px;">
                                <?= nl2br(e($m['message'])) ?>
                            </td>
                            <td>
                                <?php if (($m['status'] ?? 'unread') === 'unread'): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark">Unread</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">Read</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (($m['status'] ?? 'unread') === 'unread'): ?>
                                    <form action="messages.php" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="post_action" value="mark_read">
                                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" title="Mark Read">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDeleteMsg(<?= $m['id'] ?>)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No contact inquiries received yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMsgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-trash me-2"></i> Delete Inquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this message?</p>
            </div>
            <div class="modal-footer">
                <form action="messages.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="post_action" value="delete">
                    <input type="hidden" name="msg_id" id="deleteMsgIdInput" value="">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteMsg(id) {
    document.getElementById('deleteMsgIdInput').value = id;
    new bootstrap.Modal(document.getElementById('deleteMsgModal')).show();
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
