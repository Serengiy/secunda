<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CA;

class SetUpCommand extends Command
{
    protected $signature = 'app:set-up';

    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $data = [
            'token' => (string) Str::uuid(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = base_path('auth_token.json');
        file_put_contents($path, $json);

        $this->info('Token: ' . $data['token']);

        return CA::SUCCESS;
    }
}
