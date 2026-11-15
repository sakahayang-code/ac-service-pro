        </main>
        <!-- Footer Info for Desktop -->
        <footer class="no-print" style="padding:16px 24px; font-size:0.8rem; color:var(--text-muted); border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:var(--surface);">
            <div>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(app_config('company.name')) ?></strong>. All rights reserved.
            </div>
            <div>
                <span>Versi 1.0 (PWA Ready) &bull; Waktu Server: <?= date('d M Y H:i:s') ?> WIB</span>
            </div>
        </footer>
    </div>
</div>

<!-- Mobile Bottom Navigation (< 768px) -->
<?php require __DIR__ . '/mobile_nav.php'; ?>

<!-- Toast Notification Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Core Scripts -->
<script src="/assets/js/offline-db.js"></script>
<script src="/assets/js/app.js"></script>
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
</body>
</html>
