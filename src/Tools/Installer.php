<?php

namespace Pimlab\WebsiteSettingsBundle\Tools;

use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Extension\Bundle\Installer\OutputWriterInterface;
use Pimlab\WebsiteSettingsBundle\Service\WebsiteSettings;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Installer extends AbstractInstaller {

    use ContainerAwareTrait;

    protected $websiteSettings;

    /**
     * Installer constructor.
     * @param OutputWriterInterface|null $outputWriter
     * @param ContainerInterface $container
     */
    public function __construct(OutputWriterInterface $outputWriter = null, ContainerInterface $container, WebsiteSettings $websiteSettings){
        parent::__construct($outputWriter);
        $this->setContainer($container);
        $this->websiteSettings = $websiteSettings;
    }

    /**
     * Install process
     */
    public function install(){

        $errors = [];
        $settings = $this->container->getParameter('website_settings.settings');

        foreach ($settings as $name => $setting)
            $this->websiteSettings->create($name, $setting, $errors);


        if(!empty($errors)) {

            $message = '';
            foreach ($errors as $error)
                $message = $message.$error."\n";

            throw new \Exception("\n".$message);

        }

        $this->outputWriter->write('Website Settings successfully installed. If new Settings defined, installation can be rerun.');

    }

    /**
     * @return bool
     */
    public function isInstalled(){

        $settings = $this->container->getParameter('website_settings.settings');

        if (empty($settings))
            return true;

        foreach ($settings as $name => $setting)
            if(!$this->websiteSettings->isInstalled($name, $setting))
                return false;

        return true;

    }

    /**
     * @return bool
     */
    public function canBeInstalled(){

        if($this->isInstalled())
            return false;

        return true;

    }

    /**
     * @return bool
     */
    public function canBeUninstalled(){

        return false;

    }

}
