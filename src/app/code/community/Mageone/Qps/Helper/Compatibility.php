<?php

/**
 * Class Mageone_Qps_Helper_Compatibility
 *
 * @note POC class to handle compatibility issues with various modules
 */
class Mageone_Qps_Helper_Compatibility extends Mageone_Qps_Helper_Data
{

    const BLOCK_PREFIX = 'qps';

    /**
     * Modules with compatibility issues
     *
     * @var string[]
     */
    protected $_incompatibleModules = ['BL_CustomGrid'];

    /**
     * Status of handling compatibility for modules.
     * moduleKey => true/false (handled or not)
     *
     * @var array
     */
    protected $_handledIncompatibilities = [];

    /**
     * 1. Just report status or  0. also perform compatibility fixes
     *
     * @var bool
     */
    protected $dryRun = true;

    /**
     * Check compatibility for defined modules and apply fixes if dry-run false
     *
     * @param bool $dryRun
     * @return array
     */
    public function checkCompatibility($dryRun = true): array
    {
        $this->dryRun = $dryRun;
        foreach ($this->_incompatibleModules as $module) if ($this->isModuleEnabled($module)) {
            switch ($module) {
                case 'BL_CustomGrid':
                    $this->_handledIncompatibilities[$module] = $this->handleBlCustomGrid($this->dryRun);
                    break;
            }
            if (isset($this->_handledIncompatibilities[$module]) && $this->_handledIncompatibilities[$module] === false) {
                Mage::log(sprintf("Failed handling compatibility for %s with mageone qps.", $module));
            }
        }
        return $this->_handledIncompatibilities;
    }

    /**
     * Auto add grid exclusion if not already
     *
     * @param bool $dryRun
     * @return bool
     */
    protected function handleBlCustomGrid($dryRun = true): bool
    {
        if (!class_exists('BL_CustomGrid_Helper_Config')) {
            return false;
        }
        $gridConfig = Mage::helper('customgrid/config');
        if (!method_exists($gridConfig, 'isExcludedGrid') || !method_exists($gridConfig, 'addGridToExclusionsList')) {
            return false;
        }

        $regexWildcard = '*';

        if ($excluded = $gridConfig->isExcludedGrid(sprintf("%s/.%s", self::BLOCK_PREFIX, $regexWildcard), $regexWildcard)) { // @todo remove dot?
            return $excluded;
        }

        if (!$dryRun) {
            $gridConfig->addGridToExclusionsList(sprintf("%s/%s", self::BLOCK_PREFIX, $regexWildcard), '');
        }

        return $gridConfig->isExcludedGrid(sprintf("%s/.%s", self::BLOCK_PREFIX, $regexWildcard), $regexWildcard);
    }

}
