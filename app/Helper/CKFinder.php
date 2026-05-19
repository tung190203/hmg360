<?php

namespace App\Helper;

/**
 * Class CKFinder
 *
 * Helper class for managing CKFinder configurations and generating URLs.
 */
class CKFinder
{
    /**
     * Hashed user directory.
     *
     * @var string
     */
    protected string $hashedDirectory;

    /**
     * CKFinder constructor.
     *
     * @param string $userDirectory The user directory for CKFinder.
     */
    public function __construct(string $userDirectory)
    {
        $this->hashedDirectory = sha1($userDirectory);
    }

    /**
     * Configure the CKFinder backend using the hashed directory.
     */
    protected function configureBackend()
    {
        return [
            'name'               => 'default',
            'adapter'            => 'local',
            'baseUrl'            => $this->generateBaseUrl(),
            'root'               => $this->generateRootPath(),
            'filesystemEncoding' => 'UTF-8',
        ];
    }

    /**
     * Generate the base URL for CKFinder based on the hashed user directory.
     *
     * @return string The base URL.
     */
    protected function generateBaseUrl(): string
    {
        return "/storage/{$this->hashedDirectory}";
    }

    /**
     * Generate the root storage path for CKFinder based on the hashed user directory.
     *
     * @return string The root storage path.
     */
    protected function generateRootPath(): string
    {
        return storage_path("app/public/{$this->hashedDirectory}");
    }

    /**
     * Configure the private directory for CKFinder.
     */
    protected function configurePrivateDir()
    {
        $basePrivatePath = "ckfinder/{$this->hashedDirectory}";
        return [
            'backend' => 'laravel_cache',
            'tags'    => "{$basePrivatePath}/tags",
            'cache'   => "{$basePrivatePath}/cache",
            'thumbs'  => "{$basePrivatePath}/cache/thumbs",
            'logs'    => [
                'backend' => 'laravel_logs',
                'path'    => "{$basePrivatePath}/logs",
            ],
        ];
    }

    /**
     * Get the complete CKFinder configuration.
     *
     * @return array The complete CKFinder configuration.
     */
    public function getConfiguration(): array
    {
        return [
            'backends'   => $this->configureBackend(),
            'privateDir' => $this->configurePrivateDir(),
        ];
    }
}
