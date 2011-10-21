<?php
/**
 * @version     2011-07-23 20:07:15$
 * @author      Marek Handze
 * @package     JomSocial Group Sync
 * @copyright   Copyright (C) 2011- . All rights reserved.
 * @license     GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Installer script for package
 */
class com_JomSocialGroupSyncInstallerScript {
        
    /**
     * method to run during installation
     * installs and enables the plugin
     *
     * @return void
     */
    function install($parent)
    {
        $manifest = $parent->get("manifest");
        $parent = $parent->getParent();
        $source = $parent->getPath("source");

        $installer = new JInstaller();

        // Install plugins
        foreach($manifest->plugins->plugin as $plugin) {
            $attributes = $plugin->attributes();
            $plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
            $installer->install($plg);
        }

        $db = JFactory::getDbo();
        $tableExtensions = $db->nameQuote("#__extensions");
        $columnElement   = $db->nameQuote("element");
        $columnType      = $db->nameQuote("type");
        $columnEnabled   = $db->nameQuote("enabled");

        // Enable plugins
        $db->setQuery( "UPDATE $tableExtensions
                        SET $columnEnabled = 1
                        WHERE ( $columnElement = 'jomsocialgroupsync' OR
                                $columnElement = 'jomsocialgroupsyncsystem' ) AND
                              AND $columnType = 'plugin'"
                      );
        $db->query();
    }
}
?>