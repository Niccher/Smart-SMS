<?php
// $status may be passed directly or inherited from the parent view scope
$app    = $status['app'] ?? [];
$engine = $app['llm_engine'] ?? 'local';
$isExt  = $engine === 'external';

$providerNames = [
    'gemini'           => 'Google Gemini',
    'deepseek'         => 'DeepSeek',
    'openai'           => 'OpenAI',
    'groq'             => 'Groq',
    'mistral'          => 'Mistral AI',
    'openrouter'       => 'OpenRouter',
    'cohere'           => 'Cohere',
    'kimi'             => 'Kimi (Moonshot)',
    'nemotron'         => 'Nemotron (NVIDIA)',
    'xai'              => 'x.ai (Grok)',
    'openai-compatible'=> 'Custom API',
];

if ($isExt) {
    $engLabel  = 'External';
    $engDetail = esc($providerNames[$app['llm_external_provider'] ?? ''] ?? ($app['llm_external_provider'] ?? ''));
    $engModel  = esc($app['llm_external_model'] ?? '');
    $engColor  = '#6366f1';
    $engIcon   = 'fa-cloud';
} else {
    $engLabel  = 'Local';
    $engDetail = esc($app['llm_provider'] ?? 'llama.cpp');
    $engModel  = esc($app['llm_model'] ?? '');
    $engColor  = '#0ea5e9';
    $engIcon   = 'fa-server';
}
?>

<!-- Engine pill banner -->
<div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
    <span class="rounded-2 px-2 py-1 text-white fw-bold" style="font-size:.72rem; background:<?= $engColor ?>; letter-spacing:.03em;">
        <i class="fa-solid <?= $engIcon ?> me-1"></i><?= $engLabel ?>
    </span>
    <?php if ($engModel): ?>
    <span class="fw-semibold text-dark" style="font-size:.83rem;"><?= $engModel ?></span>
    <?php endif; ?>
    <?php if ($engDetail): ?>
    <span class="text-muted" style="font-size:.78rem;">&middot; <?= $engDetail ?></span>
    <?php endif; ?>
    <a href="<?= base_url('admin/ml/config') ?>" class="btn btn-link btn-sm p-0 ms-auto text-muted" style="font-size:.75rem;" title="Change engine">
        <i class="fa-solid fa-sliders me-1"></i>Change
    </a>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'status' ? 'active' : '' ?>" href="<?= base_url('admin/ml') ?>"><i class="fa-solid fa-gauge-high me-1"></i>Status</a></li>
    <?php if (!$isExt): ?>
        <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'models' ? 'active' : '' ?>" href="<?= base_url('admin/ml/models') ?>"><i class="fa-solid fa-database me-1"></i>Models</a></li>
    <?php endif; ?>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'config' ? 'active' : '' ?>" href="<?= base_url('admin/ml/config') ?>"><i class="fa-solid fa-sliders me-1"></i>Config</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'test' ? 'active' : '' ?>" href="<?= base_url('admin/ml/test') ?>"><i class="fa-solid fa-flask me-1"></i>Test</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'prompts' ? 'active' : '' ?>" href="<?= base_url('admin/ml/prompts') ?>"><i class="fa-solid fa-scroll me-1"></i>Prompts</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'allowed' ? 'active' : '' ?>" href="<?= base_url('admin/ml/allowed') ?>"><i class="fa-solid fa-star me-1"></i>Senders</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'jobs' ? 'active' : '' ?>" href="<?= base_url('admin/ml/jobs') ?>"><i class="fa-solid fa-briefcase me-1"></i>Jobs</a></li>
</ul>
