<?php
// admin/includes/admin_footer.php
?>
        </div> <!-- End admin-page-body -->

        <footer class="bg-white border-top py-3 px-4 text-center text-muted small mt-auto">
            PromoNest Administrator Control Center &bull; Version 2.0 &bull; Secure Session
        </footer>

    </div> <!-- End adminContent -->

</div> <!-- End admin-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('show-mobile');
        overlay.classList.toggle('show');
    }
}
</script>

</body>
</html>