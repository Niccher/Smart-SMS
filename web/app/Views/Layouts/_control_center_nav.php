<?php
$currentURI = uri_string();
if (strpos($currentURI, 'dashboard/settings/profile') !== false) {
    $activeTab = 'profile';
} elseif (strpos($currentURI, 'dashboard/settings/data') !== false) {
    $activeTab = 'data';
} elseif (strpos($currentURI, 'dashboard/devices') !== false) {
    $activeTab = 'devices';
} elseif (strpos($currentURI, 'dashboard/info') !== false) {
    $activeTab = 'tokens';
} elseif (strpos($currentURI, 'dashboard/settings') !== false) {
    $activeTab = 'preferences';
} else {
    $activeTab = 'profile';
}
?>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>" href="<?= base_url('dashboard/settings/profile') ?>">
            <i class="fa-solid fa-user-gear me-2"></i>Profile
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'devices' ? 'active' : '' ?>" href="<?= base_url('dashboard/devices') ?>">
            <i class="fa-solid fa-mobile-screen me-2"></i>Linked Devices
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'tokens' ? 'active' : '' ?>" href="<?= base_url('dashboard/info') ?>">
            <i class="fa-solid fa-key me-2"></i>API Tokens
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'preferences' ? 'active' : '' ?>" href="<?= base_url('dashboard/settings') ?>">
            <i class="fa-solid fa-sliders me-2"></i>Preferences
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'data' ? 'active' : '' ?>" href="<?= base_url('dashboard/settings/data') ?>">
            <i class="fa-solid fa-database me-2"></i>Data Management
        </a>
    </li>
</ul>
