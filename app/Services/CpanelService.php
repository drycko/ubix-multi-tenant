<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CpanelService
{
    protected $cpanelUrl;
    protected $cpanelUsername;
    protected $cpanelApiToken;
    protected $mainDomain;

    public function __construct()
    {
        $this->cpanelUrl = config('services.cpanel.url'); // e.g., 'https://yourdomain.com:2083'
        $this->cpanelUsername = config('services.cpanel.username');
        $this->cpanelApiToken = config('services.cpanel.api_token'); // Create this in cPanel
        $this->mainDomain = config('services.cpanel.main_domain'); // e.g., 'example.com'
    }

    /**
     * Create a subdomain in cPanel
     * 
     * @param string $subdomain The subdomain prefix (e.g., 'tenant1' for tenant1.example.com)
     * @param string $documentRoot The document root path (relative to home directory)
     * @return array
     */
    public function createSubdomain(string $subdomain, string $documentRoot = 'ubixcentral.example.com/public'): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->cpanelUsername . ':' . $this->cpanelApiToken,
            ])->get($this->cpanelUrl . '/execute/SubDomain/addsubdomain', [
                'domain' => $subdomain,
                'rootdomain' => $this->mainDomain,
                'dir' => $documentRoot,
                'disallowdot' => 1
            ]);

            $result = $response->json();

            if ($result['status'] == 1) {
                Log::info("Subdomain created successfully: {$subdomain}.{$this->mainDomain}");
                return [
                    'success' => true,
                    'subdomain' => "{$subdomain}.{$this->mainDomain}",
                    'data' => $result['data']
                ];
            } else {
                Log::error("Failed to create subdomain: " . json_encode($result));
                return [
                    'success' => false,
                    'error' => $result['errors'][0] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            Log::error("cPanel API error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a subdomain from cPanel
     * 
     * @param string $subdomain The full subdomain (e.g., 'tenant1.example.com')
     * @return array
     */
    public function deleteSubdomain(string $subdomain): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->cpanelUsername . ':' . $this->cpanelApiToken,
            ])->get($this->cpanelUrl . '/execute/SubDomain/delsubdomain', [
                'domain' => $subdomain
            ]);

            $result = $response->json();

            if ($result['status'] == 1) {
                Log::info("Subdomain deleted successfully: {$subdomain}");
                return [
                    'success' => true,
                    'subdomain' => $subdomain
                ];
            } else {
                Log::error("Failed to delete subdomain: " . json_encode($result));
                return [
                    'success' => false,
                    'error' => $result['errors'][0] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            Log::error("cPanel API error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List all subdomains
     * 
     * @return array
     */
    public function listSubdomains(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->cpanelUsername . ':' . $this->cpanelApiToken,
            ])->get($this->cpanelUrl . '/execute/SubDomain/listsubdomains');

            $result = $response->json();

            if ($result['status'] == 1) {
                return [
                    'success' => true,
                    'subdomains' => $result['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['errors'][0] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            Log::error("cPanel API error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
