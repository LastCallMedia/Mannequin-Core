<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Core\Ui;

use Alchemy\Zippy\Zippy;
use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Remote fetching UI.
 *
 * This class fetches the UI files from NPM if they haven't been downloaded
 * already.
 */
class RemoteUi extends LocalUi
{
    private $fetched = null;
    private $downloadDir;
    private $uiVersion;

    public function __construct($uiPath, $uiVersion = 'latest')
    {
        parent::__construct(sprintf('%s/package/build', $uiPath));
        $this->downloadDir = $uiPath;
        $this->uiVersion = $uiVersion;
    }

    public function isUiFile(string $path): bool
    {
        $this->checkFetched();

        return parent::isUiFile($path);
    }

    public function files(): array
    {
        $this->checkFetched();

        return parent::files();
    }

    private function checkFetched()
    {
        if (null === $this->fetched) {
            $this->fetched = file_exists(sprintf('%s/package/package.json', $this->downloadDir));
            if (!$this->fetched) {
                $url = $this->getFetchUrl();
                if ($tmpFile = $this->fetch($url)) {
                    $this->fetched = $this->extract($tmpFile, $this->downloadDir);
                }
            }
            if (!$this->fetched) {
                throw new \RuntimeException(sprintf('Unable to download UI package from %s to %s', $this->fetchUrl, $this->downloadDir));
            }
        }
    }

    private function getFetchUrl()
    {
        $client = new Client();
        if ($response = $client->get('https://registry.npmjs.org/lastcall-mannequin-ui')) {
            $contents = \GuzzleHttp\json_decode($response->getBody(), true);
            $version = 'invalid';
            if (isset($contents['versions'][$this->uiVersion])) {
                $version = $this->uiVersion;
            } elseif (isset($contents['versions']['dist-tags'][$this->uiVersion])) {
                $version = $contents['versions']['dist-tags'][$this->uiVersion];
            }
            if ($version === 'invalid') {
                throw new \RuntimeException(sprintf('Unable to find requested UI version: %s', $this->uiVersion));
            }

            return $contents['versions'][$version]['dist']['tarball'];
        }
        throw new \RuntimeException('Invalid response from NPM server.');
    }

    private function fetch($url)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'mannequin-ui');
        $zipFile = $tmpFile.pathinfo($url, PATHINFO_BASENAME);
        rename($tmpFile, $zipFile);
        (new Client())->get($url, ['save_to' => $zipFile]);

        return $zipFile;
    }

    private function extract($tmpFile, $destination)
    {
        (new Filesystem())->mkdir($destination);
        $archive = Zippy::load()->open($tmpFile);
        $archive->extract($destination);

        return true;
    }
}
