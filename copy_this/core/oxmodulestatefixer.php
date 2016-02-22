<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Module state fixer
 */
class oxModuleStateFixer extends oxModuleInstaller
{
    /** @var oxIOutput $_debugOutput */
    protected $_debugOutput;


    /**
     * Fix module states task runs version, extend, files, templates, blocks,
     * settings and events information fix tasks
     *
     * @param oxModule      $oModule
     * @param oxConfig|null $oConfig If not passed uses default base shop config
     */
    public function fix(oxModule $oModule, oxConfig $oConfig = null)
    {
        if ($oConfig !== null) {
            $this->setConfig($oConfig);
        }

        $sModuleId = $oModule->getId();

        $this->_deleteBlock($sModuleId);
        $this->_deleteTemplateFiles($sModuleId);
        $this->_deleteModuleFiles($sModuleId);
        $this->_deleteModuleEvents($sModuleId);

        $this->_addExtensions($oModule);

        $this->_addTemplateBlocks($oModule->getInfo("blocks"), $sModuleId);
        $this->_addModuleFiles($oModule->getInfo("files"), $sModuleId);
        $this->_addTemplateFiles($oModule->getInfo("templates"), $sModuleId);
        $this->_addModuleSettings($oModule->getInfo("settings"), $sModuleId);
        $this->_addModuleVersion($oModule->getInfo("version"), $sModuleId);
        $this->_addModuleEvents($oModule->getInfo("events"), $sModuleId);

        /** @var oxModuleCache $oModuleCache */
        $oModuleCache = oxNew('oxModuleCache', $oModule);
        $oModuleCache->resetCache();
    }

    public function setDebugOutput(oxIOutput $o)
    {
        $this->_debugOutput = $o;
    }

    /**
     * Add extension to module
     * overriden to have the ability to save config only when needed
     * and the output that info
     * @param oxModule $oModule
     */
    protected function _addExtensions(oxModule $oModule)
    {
        $aModulesDefault = $this->getConfig()->getConfigParam('aModules');
        $aModules = $this->getModulesWithExtendedClass();
        $aModules = $this->_removeNotUsedExtensions($aModules, $oModule);


        if ($oModule->hasExtendClass()) {
            $aAddModules = $oModule->getExtensions();
            $aModules = $this->_mergeModuleArrays($aModules, $aAddModules);
        }

        $aModules = $this->buildModuleChains($aModules);
        if ($aModulesDefault != $aModules) {
            $result=array_diff($aModules,$aModulesDefault);
            if ($this->_debugOutput) {
                $this->_debugOutput->writeLn("[INFO] fixing " . $oModule->getId());
            }
            $this->_saveToConfig('aModules', $aModules);
        }
    }

    protected function _removeGarbage($aInstalledExtensions, $aGarbage)
    {
        if ($this->_debugOutput) {
            $this->_debugOutput->writeLn("[INFO] removing garbage: " . join(',', $aGarbage));
        }
        parent::_removeGarbage($aInstalledExtensions, $aGarbage);
    }
}
