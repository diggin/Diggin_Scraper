<?php
/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */
$package->channel = $compatible->channel 
    = 'pear.diggin.musicrider.com';
$package->rawlead = $compatible->rawlead
    = array(
    'name' => 'sasezaki',
    'user' => 'sasezaki',
    'email' => 'sasezaki@gmail.com',
    'active' => 'yes'
);
$package->license = $compatible->license
    = 'New BSD License';
$package->dependencies['required']->php = $compatible->dependencies['required']->php
    = '5.3.3';
$package->summary = $compatible->summary
    = "web-sraping component";
$package->description = $compatible->description
    = "web-sraping component, inspired by Perl’s Web::Scraper. It provides a DSL-ish interface for traversing HTML documents and returning a neatly arranged PHP ‘s multidimensional array";
$package->notes = $compatible->notes
    = "developing";

/**
$package->dependencies['required']->extension['mbstring']->save();
$compatible->dependencies['required']->extension['mbstring']->save();
$package->dependencies['required']->extension['iconv']->save();
$compatible->dependencies['required']->extension['iconv']->save();
*/
