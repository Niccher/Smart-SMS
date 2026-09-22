<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Analysis is Ready! 🤖</h1>
<p class="lead">Your M-Pesa transaction analysis has finished processing. New insights and categorized transactions are now available.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Analysis Summary</h3>
    <div class="info-row">
        <span class="info-label">Messages Processed</span>
        <span class="info-value"><?= esc(number_format($messagesProcessed ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">New Transactions</span>
        <span class="info-value"><?= esc(number_format($newTransactions ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Categories Identified</span>
        <span class="info-value"><?= esc(number_format($categoriesFound ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Processing Time</span>
        <span class="info-value"><?= esc($duration ?? 'N/A') ?></span>
    </div>
</div>
<a href="<?= esc(base_url('dashboard')) ?>" class="button">View Insights</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Your data was analyzed using our ML backend. Results may improve over time as the model learns from your spending patterns.</p>
