<?php

namespace App\Controllers;

use App\Models\UploadModel;

class Info extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();
        $mod_uploads = new UploadModel();
        $user = auth()->user();
        
        // Fetch last upload details
        $lastUpload = $db->table('tbl_Loot_Summary')
            ->orderBy('loot_Created', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
            
        // Count active devices (unique UUIDs in summary)
        $activeDevicesCount = $db->table('tbl_Loot_Summary')
            ->select('COUNT(DISTINCT loot_Uuid) as device_count')
            ->get()
            ->getRow()
            ->device_count ?? 0;
            
        // Active tokens
        $tokens = $user ? $user->accessTokens() : [];

        $data = [
            'total_processed' => $mod_uploads->countAll(),
            'last_upload' => $lastUpload,
            'active_devices_count' => $activeDevicesCount,
            'tokens' => $tokens,
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/info', $data);
    }
    
    public function generateToken()
    {
        $user = auth()->user();
        if ($user) {
            // Revoke old ones to single-device it, or keep it multi-device
            $user->revokeAllAccessTokens();
            
            // Generate a Custom 12-character Alphanumeric Token
            helper('text');
            $rawToken = random_string('alnum', 12);
            $hashedToken = hash('sha256', $rawToken);
            
            $db = \Config\Database::connect();
            $db->table('auth_identities')->insert([
                'user_id' => $user->id,
                'type' => \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN,
                'name' => 'Android_App_Device',
                'secret' => $hashedToken,
                'extra' => serialize(['*']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Persist the raw token → user link so uploaded data stays
            // attributable even after this Shield token is revoked.
            $modUploads = new UploadModel();
            $modUploads->linkDevice((int)$user->id, $rawToken, 'Android App Device');
            
            return redirect()->to(url_to('Info::index'))->with('new_token', $rawToken);
        }
        return redirect()->to(url_to('Info::index'))->with('error', 'Authentication Error');
    }
    
    public function revokeToken()
    {
        $user = auth()->user();
        if ($user) {
            $user->revokeAllAccessTokens();
            return redirect()->to(url_to('Info::index'))->with('message', 'API Token successfully nullified.');
        }
        return redirect()->to(url_to('Info::index'))->with('error', 'Authentication Error');
    }
}
