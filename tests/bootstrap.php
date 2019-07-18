<?php
declare(strict_types=1);

/*
 * Test suite bootstrap
 *
 */

use Cake\Core\Configure;

/*
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);
require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

define('PLUGIN_ROOT', $root . DS);
define('PLUGIN_TESTS', $root . DS . 'tests' . DS);

Configure::write('App.paths.plugins', [PLUGIN_TESTS . 'test_app' . DS . 'plugins' . DS]);
