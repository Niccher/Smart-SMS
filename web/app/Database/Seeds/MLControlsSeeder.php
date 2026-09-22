<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds default ML backend control flags into tbl_ML_Controls.
 *
 * These key/value pairs are read by the Python ML backend on startup
 * (and on reload) via config.py::reload_from_db(). DB values override
 * environment variables, so setting them here gives the admin panel
 * full control without needing a redeploy.
 *
 * Keys map 1-to-1 with Settings attributes in the ML backend config.py.
 * Only non-sensitive tuning defaults are seeded here. Secrets (API keys)
 * are intentionally left empty — they must be set through the admin panel
 * or Railway environment variables.
 *
 * All rows are idempotent — existing keys are left untouched.
 */
class MLControlsSeeder extends Seeder
{
    private const CONTROLS = [
        // ── LLM engine selection ─────────────────────────────────────────
        // 'local'    = llama.cpp / local GGUF model
        // 'external' = cloud API (Gemini, OpenAI, Groq, etc.)
        ['control_key' => 'llm_engine',   'control_value' => 'external'],

        // ── External cloud provider (when llm_engine = external) ─────────
        // Options: openai | gemini | groq | openrouter | mistral | cohere |
        //          deepseek | xai | kimi | nemotron | openai-compatible
        ['control_key' => 'llm_external_provider',    'control_value' => 'gemini'],
        ['control_key' => 'llm_external_model',       'control_value' => 'gemini-2.0-flash'],
        ['control_key' => 'llm_external_max_tokens',  'control_value' => '4096'],
        ['control_key' => 'llm_external_temperature', 'control_value' => '0.1'],

        // ── Batch / concurrency tuning ────────────────────────────────────
        // batch_size: number of SMS sent in a single LLM call
        ['control_key' => 'batch_size',           'control_value' => '5'],
        // external_batch_size: batch size when using a cloud provider
        ['control_key' => 'external_batch_size',  'control_value' => '20'],
        ['control_key' => 'max_retries',          'control_value' => '3'],
        ['control_key' => 'external_max_retries', 'control_value' => '3'],
        // poll_interval: seconds between poller cycles
        ['control_key' => 'poll_interval',          'control_value' => '30'],
        ['control_key' => 'external_poll_interval', 'control_value' => '15'],

        // ── Local engine tuning (only used when llm_engine = local) ──────
        ['control_key' => 'llm_max_tokens',  'control_value' => '2048'],
        ['control_key' => 'llm_temperature', 'control_value' => '0.2'],
        ['control_key' => 'llm_ctx_size',    'control_value' => '16384'],
        ['control_key' => 'llm_batch_size',  'control_value' => '512'],
        ['control_key' => 'n_gpu_layers',    'control_value' => '0'],

        // ── Fallback provider ─────────────────────────────────────────────
        ['control_key' => 'llm_fallback_enabled',  'control_value' => 'false'],
        ['control_key' => 'llm_fallback_provider', 'control_value' => 'groq'],
        ['control_key' => 'llm_fallback_model',    'control_value' => 'llama-3.1-8b-instant'],
        ['control_key' => 'llm_fallback_base_url', 'control_value' => 'https://api.groq.com/openai/v1'],
    ];

    public function run(): void
    {
        $db       = \Config\Database::connect();
        $now      = date('Y-m-d H:i:s');
        $inserted = 0;
        $skipped  = 0;

        foreach (self::CONTROLS as $row) {
            $exists = $db->table('tbl_ML_Controls')
                ->where('control_key', $row['control_key'])
                ->get()->getRow();

            if ($exists) {
                $skipped++;
                continue;
            }

            $db->table('tbl_ML_Controls')->insert([
                'control_key'   => $row['control_key'],
                'control_value' => $row['control_value'],
                'updated_at'    => $now,
            ]);
            $inserted++;
        }

        echo "MLControlsSeeder: inserted {$inserted}, skipped {$skipped}." . PHP_EOL;
    }
}
