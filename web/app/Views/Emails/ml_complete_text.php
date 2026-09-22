Your Analysis is Ready!

Your M-Pesa transaction analysis has finished processing. New insights and categorized transactions are now available.

Analysis Summary:
- Messages Processed: <?= esc(number_format($messagesProcessed ?? 0)) ?>
- New Transactions: <?= esc(number_format($newTransactions ?? 0)) ?>
- Categories Identified: <?= esc(number_format($categoriesFound ?? 0)) ?>
- Processing Time: <?= esc($duration ?? 'N/A') ?>

View Insights: <?= esc(base_url('dashboard')) ?>

Your data was analyzed using our ML backend. Results may improve over time as the model learns from your spending patterns.

Email ID: ml_complete
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>