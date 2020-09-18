<?php

namespace Pimlab\WebsiteSettingsBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimlab\WebsiteSettingsBundle\Tools\Installer;

class WebsiteSettingsBundle extends AbstractPimcoreBundle{

    use PackageVersionTrait;

    const PACKAGE_NAME = 'pimlab/website-settings';

    const BUNDLE_VERSION = '1.0.4';

    /**
     * @return object|\Pimcore\Extension\Bundle\Installer\InstallerInterface|null
     */
    public function getInstaller(){
        return $this->container->get(Installer::class);
    }

    /**
     * @return string
     */
    public function getVersion(){
        return self::BUNDLE_VERSION;
    }

    /**
     * @return string
     */
    public function getDescription(){
        return 'Installs predefined Website Settings. If green (+) is visible, they are Settings to install.';
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName(): string {
        return self::PACKAGE_NAME;
    }
}
