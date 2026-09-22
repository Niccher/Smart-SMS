<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'db-info' ? 'active' : '' ?>" href="<?= base_url('admin/system/db-info') ?>"><i class="fa-solid fa-database me-1"></i>DB Info</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'maintenance' ? 'active' : '' ?>" href="<?= base_url('admin/system/maintenance') ?>"><i class="fa-solid fa-broom me-1"></i>Cache & Sessions</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'backup' ? 'active' : '' ?>" href="<?= base_url('admin/system/backup') ?>"><i class="fa-solid fa-file-shield me-1"></i>DB Backup</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'logs' ? 'active' : '' ?>" href="<?= base_url('admin/system/logs') ?>"><i class="fa-solid fa-file-lines me-1"></i>Log Viewer</a></li>
</ul>
