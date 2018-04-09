<?php

/*
 * This file is part of the Panthère project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Panthere\ProcessManager;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Symfony\Component\Process\Process;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class FirefoxManager implements BrowserManagerInterface
{
    use WebServerReadinessProbeTrait;

    private $process;

    public function __construct(?string $geckodriverBinary = null)
    {
        $this->process = new Process([$geckodriverBinary ?? $this->findGeckodriverBinary()], null, null, null, null);
    }

    /**
     * @param string[]|null $arguments
     *
     * @throws \RuntimeException
     */
    public function start(): WebDriver
    {
        if (!$this->process->isRunning()) {
            $this->checkPortAvailable('127.0.0.1', 4444);
            $this->process->start();
            $this->waitUntilReady($this->process, 'http://127.0.0.1:4444/status');
        }

        return RemoteWebDriver::create('http://localhost:4444', DesiredCapabilities::firefox(), null, null, null, null, null, true);
    }

    public function quit(): void
    {
        $this->process->stop();
    }

    private function findGeckodriverBinary(): string
    {
        switch (PHP_OS_FAMILY) {
            case 'Windows':
                return __DIR__.'/../../geckodriver-bin/geckodriver.exe';

            case 'Darwin':
                return __DIR__.'/../../geckodriver-bin/geckodriver-macos';

            default:
                return __DIR__.'/../../geckodriver-bin/geckodriver-linux64';
        }
    }
}
