<?php

namespace App\Studio\Discovery;

use App\Kernel\Contracts\Command;
use ReflectionClass;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class CommandDiscoverer
{
    private string $domainPath;

    public function __construct()
    {
        $this->domainPath = app_path('Domain');
    }

    /**
     * Discover all commands in the App\Domain namespace.
     */
    public function discover(): array
    {
        if (!is_dir($this->domainPath)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($this->domainPath)->path('/Commands/')->name('*.php');

        $commands = [];

        foreach ($finder as $file) {
            $class = $this->getClassFromFile($file->getRealPath());

            if ($class && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                
                // Only include if it's a concrete class (could also check interface implementation)
                if (!$reflection->isAbstract()) {
                    $commands[] = $this->extractMetadata($class, $reflection);
                }
            }
        }

        return $commands;
    }

    private function extractMetadata(string $class, ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();
        $arguments = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $arguments[] = [
                    'name' => $param->getName(),
                    'type' => $param->getType() ? (string) $param->getType() : 'string',
                    'required' => !$param->isOptional(),
                ];
            }
        }

        // Determine Domain from Namespace
        // App\Domain\Customer\Commands\RegisterCustomer -> Customer
        $parts = explode('\\', $class);
        $domain = $parts[2] ?? 'General';

        return [
            'class' => $class,
            'name' => Str::title(Str::replace(['Command', 'Application'], '', $reflection->getShortName())),
            'domain' => $domain,
            'arguments' => $arguments,
        ];
    }

    private function getClassFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);
        if (preg_match('/namespace\s+(.+?);/i', $contents, $matches)) {
            $namespace = trim($matches[1]);
            if (preg_match('/class\s+(\w+)/i', $contents, $classMatches)) {
                return $namespace . '\\' . $classMatches[1];
            }
        }
        return null;
    }
}
