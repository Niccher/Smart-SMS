<?php

namespace App\Database\Seeds;

class AllowedSendersSeeder extends \CodeIgniter\Database\Seeder
{
    private const SENDERS = [
        ['sender' => 'MPESA',         'category' => 'Mobile Money'],
        ['sender' => 'AIRTELMONEY',   'category' => 'Mobile Money'],
        ['sender' => 'AIRTEL MONEY',  'category' => 'Mobile Money'],
        ['sender' => 'T-KASH',        'category' => 'Mobile Money'],
        ['sender' => 'TELKOM',        'category' => 'Mobile Money'],
        ['sender' => 'KCB',               'category' => 'Bank'],
        ['sender' => 'EQUITY',            'category' => 'Bank'],
        ['sender' => 'NCBA',              'category' => 'Bank'],
        ['sender' => 'LOOP',              'category' => 'Bank'],
        ['sender' => 'ABSA',              'category' => 'Bank'],
        ['sender' => 'COOP',              'category' => 'Bank'],
        ['sender' => 'DTB',               'category' => 'Bank'],
        ['sender' => 'FAMILY BANK',       'category' => 'Bank'],
        ['sender' => 'I&M',               'category' => 'Bank'],
        ['sender' => 'STANBIC',           'category' => 'Bank'],
        ['sender' => 'STANCHART',         'category' => 'Bank'],
        ['sender' => 'SIDIAN',            'category' => 'Bank'],
        ['sender' => 'GULF AFRICAN',      'category' => 'Bank'],
        ['sender' => 'NATIONAL BANK',     'category' => 'Bank'],
        ['sender' => 'NBK',               'category' => 'Bank'],
        ['sender' => 'HFC',               'category' => 'Bank'],
        ['sender' => 'PRIME BANK',        'category' => 'Bank'],
        ['sender' => 'CREDIT BANK',       'category' => 'Bank'],
        ['sender' => 'CONSOLIDATED BANK', 'category' => 'Bank'],
        ['sender' => 'BANK OF AFRICA',    'category' => 'Bank'],
        ['sender' => 'BOA',               'category' => 'Bank'],
        ['sender' => 'ECOBANK',           'category' => 'Bank'],
        ['sender' => 'UBA',               'category' => 'Bank'],
        ['sender' => 'GTBANK',            'category' => 'Bank'],
        ['sender' => 'VICTORIA BANK',     'category' => 'Bank'],
        ['sender' => 'PARAMOUNT BANK',    'category' => 'Bank'],
        ['sender' => 'HOUSING FINANCE',   'category' => 'Bank'],
        ['sender' => 'NCBA_BANK',         'category' => 'Bank'],
        ['sender' => 'NCBA_INFO',         'category' => 'Bank'],
        ['sender' => 'IANDMBANK',         'category' => 'Bank'],
        ['sender' => 'KCBINFO',           'category' => 'Bank'],
        ['sender' => 'EQUITY BANK',       'category' => 'Bank'],
        ['sender' => 'MSHWARI',       'category' => 'Fintech'],
        ['sender' => 'M-SHWARI',      'category' => 'Fintech'],
        ['sender' => 'TALA',          'category' => 'Fintech'],
        ['sender' => 'BRANCH',        'category' => 'Fintech'],
        ['sender' => 'ZENKA',         'category' => 'Fintech'],
        ['sender' => 'TIMIZA',        'category' => 'Fintech'],
        ['sender' => 'HUSTLER FUND',  'category' => 'Fintech'],
        ['sender' => 'HUSTLERFUND',   'category' => 'Fintech'],
        ['sender' => 'STAWI',         'category' => 'Fintech'],
        ['sender' => 'OKASH',         'category' => 'Fintech'],
        ['sender' => 'CREDITBEE',     'category' => 'Fintech'],
        ['sender' => 'PESAPAL',       'category' => 'Fintech'],
        ['sender' => 'JENGA',         'category' => 'Fintech'],
        ['sender' => 'KCB M-PESA',    'category' => 'Fintech'],
        ['sender' => 'OKOA',          'category' => 'Fintech'],
        ['sender' => 'FULIZA',        'category' => 'Fintech'],
        ['sender' => 'GLOBALPAY',     'category' => 'Fintech'],
        ['sender' => 'STIMA SACCO',        'category' => 'SACCO'],
        ['sender' => 'MWALIMU SACCO',      'category' => 'SACCO'],
        ['sender' => 'UNAITAS',            'category' => 'SACCO'],
        ['sender' => 'HARAMBEE SACCO',     'category' => 'SACCO'],
        ['sender' => 'KENYA POLICE SACCO', 'category' => 'SACCO'],
        ['sender' => 'AFYA SACCO',         'category' => 'SACCO'],
        ['sender' => 'SAFARICOM SACCO',    'category' => 'SACCO'],
        ['sender' => 'BRITAM',            'category' => 'Insurance'],
        ['sender' => 'JUBILEE INSURANCE', 'category' => 'Insurance'],
        ['sender' => 'CIC INSURANCE',     'category' => 'Insurance'],
        ['sender' => 'APA INSURANCE',     'category' => 'Insurance'],
        ['sender' => 'UAP OLD MUTUAL',    'category' => 'Insurance'],
        ['sender' => 'MADISON',           'category' => 'Insurance'],
        ['sender' => 'PESALINK',  'category' => 'Payments/Govt'],
        ['sender' => 'PESAFLOW',  'category' => 'Payments/Govt'],
        ['sender' => 'KRA',       'category' => 'Payments/Govt'],
        ['sender' => 'ECITIZEN',  'category' => 'Payments/Govt'],
    ];

    public function run()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $inserted = 0;
        $skipped  = 0;

        foreach (self::SENDERS as $row) {
            $exists = $db->table('tbl_Allowed_Senders')
                ->where('sender', $row['sender'])
                ->get()->getRow();

            if ($exists) { $skipped++; continue; }

            $db->table('tbl_Allowed_Senders')->insert([
                'sender'     => $row['sender'],
                'category'   => $row['category'],
                'created_at' => $now,
            ]);
            $inserted++;
        }

        echo "AllowedSendersSeeder: inserted {$inserted}, skipped {$skipped}." . PHP_EOL;
    }
}
