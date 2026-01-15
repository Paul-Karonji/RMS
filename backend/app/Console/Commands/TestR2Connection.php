<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestR2Connection extends Command
{
    protected $signature = 'test:r2';
    protected $description = 'Test Cloudflare R2 connection';

    public function handle()
    {
        $this->info('Testing Cloudflare R2 connection...');

        try {
            // Test 1: Create a test file
            $testContent = 'RMS Test File - ' . now()->toDateTimeString();
            $testPath = 'test/connection-test.txt';
            
            $this->info('1. Uploading test file...');
            Storage::disk('r2')->put($testPath, $testContent);
            $this->info('   ✅ File uploaded successfully');

            // Test 2: Check if file exists
            $this->info('2. Checking if file exists...');
            if (Storage::disk('r2')->exists($testPath)) {
                $this->info('   ✅ File exists');
            } else {
                $this->error('   ❌ File not found');
                return 1;
            }

            // Test 3: Read file content
            $this->info('3. Reading file content...');
            $content = Storage::disk('r2')->get($testPath);
            if ($content === $testContent) {
                $this->info('   ✅ Content matches');
            } else {
                $this->error('   ❌ Content mismatch');
                return 1;
            }

            // Test 4: Get file URL
            $this->info('4. Getting file URL...');
            $url = Storage::disk('r2')->url($testPath);
            $this->info('   URL: ' . $url);

            // Test 5: Delete test file
            $this->info('5. Cleaning up test file...');
            Storage::disk('r2')->delete($testPath);
            $this->info('   ✅ File deleted');

            $this->newLine();
            $this->info('🎉 All tests passed! Cloudflare R2 is configured correctly.');
            $this->info('Bucket: ' . config('filesystems.disks.r2.bucket'));
            $this->info('Endpoint: ' . config('filesystems.disks.r2.endpoint'));

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ R2 Connection Failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Troubleshooting:');
            $this->warn('1. Check AWS_ACCESS_KEY_ID in .env');
            $this->warn('2. Check AWS_SECRET_ACCESS_KEY in .env');
            $this->warn('3. Check AWS_ENDPOINT in .env');
            $this->warn('4. Run: php artisan config:clear');
            
            return 1;
        }
    }
}
