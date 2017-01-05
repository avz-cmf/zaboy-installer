<?php
/**
 *
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 27.12.16
 * Time: 4:02 PM
 */

namespace zaboy\installer\Install;

use Composer\Script\Event;
use FilesystemIterator;
use Interop\Container\ContainerInterface;
use RecursiveDirectoryIterator;

abstract class AbstractCommand
{

    const INSTALL = 'install';

    const UNINSTALL = 'uninstall';

    const REINSTALL = 'reinstall';

    /**
     * avz-cmf [lib-name] => [
     *      "class" => 'InstallerCommands::Class'
     *      "installed" => true|false
     * ]
     * @var array
     **/
    protected static $dep = [];

    /** @var ContainerInterface */
    private static $container = null;

    /**
     * @param Event $event
     * @return void
     */
    public static function install(Event $event)
    {
        try {
            static::command($event, self::INSTALL);
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, self::UNINSTALL);
        }
    }

    /**
     * do command for include installers.
     * Composer Event - for get dependencies and IO
     * @param Event $event
     * Type of command doю
     * @param $commandType
     */
    protected static function command(Event $event, $commandType)
    {
        //founds dep installer only if app
        if (!static::isLib()) {
            $composer = $event->getComposer();
            $localRep = $composer->getRepositoryManager()->getLocalRepository();
            //get all dep lis (include dependency of dependency)
            $dependencies = $localRep->getPackages();
            foreach ($dependencies as $dependency) {
                $target = $dependency->getPrettyName();
                $match = [];
                //get dependencies who has InstallCommands
                $path = realpath('vendor') . DIRECTORY_SEPARATOR .
                    $target . DIRECTORY_SEPARATOR .
                    'src' . DIRECTORY_SEPARATOR .
                    'InstallCommands.php';
                if (preg_match('/^[\w\-\_]\/([\w\-\_]+)$/', $target, $match) && file_exists($path)) {
                    if (!isset(AbstractCommand::$dep[$match[1]])) {
                        $class = $match[1] . '\\InstallCommands';
                        AbstractCommand::$dep[$match[1]] = [
                            "class" => $class
                        ];
                        AbstractCommand::$dep[$match[1]]['installed'] = class_exists($class) ? 0 : -1;
                    }
                    //call command recursive by dep
                    if (AbstractCommand::$dep[$match[1]]['installed'] == 0) {
                        /** @var AbstractCommand $installer */
                        call_user_func([AbstractCommand::$dep[$match[1]]['class'], $commandType], $event);
                    }
                }
                //}
            }
        }

        $installers = static::getInstallers();
        /** @var InstallerInterface $installer */
        foreach ($installers as $installerClass) {
            $installer = new $installerClass(self::getContainer());
            $installer->{$commandType}();
        }
    }

    /**
     * Return
     * @return string
     */
    public static function isLib()
    {
        return preg_match('/\/vendor\//', __DIR__) == 1;
    }

    /**
     * return array with Install class for lib;
     * dir - for search Installer automate
     * @param string $dir
     * @return InstallerInterface[]
     */
    public static function getInstallers($dir = null)
    {

        $installer = [];
        if (!isset($dir)) {
            $dir = __DIR__;
        }
        //create template path for search Installer class
        $reflector = new \ReflectionClass(static::class);
        $namespace = $reflector->getNamespaceName();
        $classPath = $reflector->getFileName();
        $className = basename($classPath);
        $srcRoot = substr($classPath, 0, strlen($classPath) - strlen($className) - 1);

        $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS |
            FilesystemIterator::KEY_AS_PATHNAME);

        foreach ($iterator as $item) {
            //Save only class who implement InstallerInterface and has Installer in name
            /** @var $item RecursiveDirectoryIterator */
            if (!preg_match('/^(\.)|(vendor)/', $item->getFilename())) {
                if ($item->isDir()) {
                    $installer = array_merge($installer, static::getInstallers($item->getPathname()));
                } elseif (preg_match('/Installer/', $item->getFilename())) {
                    $path = substr($item->getPath(), strlen($srcRoot));
                    $namespace_ = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', $path);
                    $class = $namespace_ . '\\' . $item->getBasename('.php');
                    $reflector = new \ReflectionClass($class);
                    if ($reflector->implementsInterface(InstallerInterface::class) &&
                        !$reflector->isAbstract() && !$reflector->isInterface()
                    ) {
                        $installer[] = $reflector->getName();
                    }
                }
            }
        }
        return $installer;
    }

    /**
     * @return ContainerInterface
     */
    private static function getContainer()
    {
        if (!isset(AbstractCommand::$container)) {
            AbstractCommand::$container = include 'config/container.php';
        }

        return AbstractCommand::$container;
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function uninstall(Event $event)
    {
        static::command($event, self::UNINSTALL);
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function reinstall(Event $event)
    {
        try {
            static::command($event, self::REINSTALL);
        } catch (\Exception $exception) {
            $event->getIO()->writeError("Installing error: \n" . $exception->getMessage() . "\nUninstalling changes.");
            static::command($event, self::UNINSTALL);
        }

    }

    public static function getPublicDir()
    {
        /**
         * Have a list of names of public directories.
         * Iterate through the directory to check their availability and presence in her file index.php
         */
        $publicDirs = [
            'www',
            'public',
            'web',
        ];
        foreach ($publicDirs as $publicDir) {
            if (is_dir($publicDir) && file_exists($publicDir . DIRECTORY_SEPARATOR . "index.php")) {
                return realpath($publicDir);
            }
        }
        throw new \Exception("The public directory was not found");
    }
}
