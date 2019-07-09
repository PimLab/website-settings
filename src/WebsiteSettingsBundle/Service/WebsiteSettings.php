<?php

namespace Pimlab\WebsiteSettingsBundle\Service;

use Pimcore\Log\ApplicationLogger;
use Pimcore\Model\Site;
use Pimcore\Model\WebsiteSetting;
use Pimcore\Tool;

class WebsiteSettings {

    /**
     * @var ApplicationLogger
     */
    protected $applicationLogger;

    /**
     * @var array
     */
    private $validKeys = ['data','type', 'multilang', 'multisite'];

    /**
     * @var array
     */
    private $validTypes = ['text','document','asset','object','bool'];

    /**
     * @var string[]
     */
    protected $validLanguages;

    /**
     * @var array|Site[]
     */
    protected $validSites;

    /**
     * WebsiteSettings constructor.
     * @param ApplicationLogger $applicationLogger
     */
    public function __construct() {
        $this->applicationLogger = ApplicationLogger::getInstance('pimlab.website_settings');
        $this->validLanguages = Tool::getValidLanguages();
        $this->validSites = $this->getPimcoreSites();
    }

    /**
     * @param string $name
     * @param int|null $siteId
     * @param string|null $lang
     * @return bool
     */
    public function settingExists(string $name, ?int $siteId = null, ?string $lang = null) {

        $ws = WebsiteSetting::getByName($name, $siteId, $lang);

        if(is_null($ws))
            return false;

        return true;

    }

    /**
     * @param string $name
     * @param array $setting
     * @param array $errors
     */
    public function create(string $name, array $setting, array &$errors = []) {

        if(true !== $result = $this->validateSetting($name, $setting)) {
            $errors[] = $result;
            return;
        }

        $multilang = ($setting['multilang'] ? true : false );
        $multisite = ($setting['multisite'] ? true : false );

        if ($multilang && $multisite) {

            foreach ($this->validLanguages as $language)
                foreach ($this->validSites as $site)
                    $this->addNewSettingIfNotExists($name, $setting, $site->getId(), $language);

        } elseif ($multilang && !$multisite) {

            foreach ($this->validLanguages as $language)
                $this->addNewSettingIfNotExists($name, $setting, null, $language);

        } elseif (!$multilang && $multisite) {

            foreach ($this->validSites as $site)
                $this->addNewSettingIfNotExists($name, $setting, $site->getId(), null);

        } else {

            $this->addNewSettingIfNotExists($name, $setting, null, null);

        }

    }

    /**
     * @param $name
     * @param $setting
     * @param int|null $siteId
     * @param string|null $language
     */
    private function addNewSettingIfNotExists($name, $setting, ?int $siteId = null, ?string $language = null) {

        if($this->settingExists($name, $siteId, $language))
            return;

        $ws = new WebsiteSetting();
        $ws->setName($name);
        $ws->setType($setting['type']);

        if(!empty($setting['data']))
            $ws->setData($setting['data']);

        if(!empty($siteId))
            $ws->setSiteId($siteId);

        if(!empty($language))
            $ws->setLanguage($language);

        $ws->save();

    }

    /**
     * @param string $name
     * @param array $setting
     * @return bool|string
     */
    private function validateSetting(string $name, array $setting) {

        $message = "";

        if(!in_array($setting['type'], $this->validTypes))
            $message = $message." Settings-Type '".$setting['type']."' is not valid. Valid Types: ".implode(", ", $this->validTypes);

        if(!empty($message)){
            $message = $name.": ".trim($message);
            return $message;
        }

        return true;

    }

    /**
     * @return array|Site[]
     */
    private function getPimcoreSites() {

        try {
            $siteList = new Site\Listing();
        } catch (\Exception $e) {
            $this->applicationLogger->error("WebsiteSettingsBundle: ".$e->getMessage());
            return [];
        }

        $sites = $siteList->load();

        if(empty($sites))
            return [];

        return $sites;

    }

    /**
     * @param string $name
     * @param array $setting
     * @return bool
     */
    public function isInstalled(string $name, array $setting) {

        $multilang = ($setting['multilang'] ? true : false );
        $multisite = ($setting['multisite'] ? true : false );

        if ($multilang && $multisite) {

            foreach ($this->validLanguages as $language)
                foreach ($this->validSites as $site)
                    if(!$this->settingExists($name, $site->getId(), $language))
                        return false;

        } elseif ($multilang && !$multisite) {

            foreach ($this->validLanguages as $language)
                if(!$this->settingExists($name, null, $language))
                    return false;

        } elseif (!$multilang && $multisite) {

            foreach ($this->validSites as $site)
                if(!$this->settingExists($name, $site->getId(), null))
                    return false;

        } else {

            if(!$this->settingExists($name, null, null))
                return false;

        }

        return true;

    }

}