<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 5:27 PM
 */

namespace zaboy\installer\Install;


use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;

abstract class InstallerAbstract implements InstallerInterface
{

    /** @var ContainerInterface  */
    protected $container;

    /** @var IOInterface  */
    protected $io;

    /**
     * Installer constructor.
     * @param ContainerInterface $container
     * @param IOInterface $io
     * @internal param IOInterface $IO
     */
    public function __construct(ContainerInterface $container, IOInterface $io)
    {
        $this->io = $io;
        $this->container = $container;
    }

    /**
     * Make clean and install.
     * @return void
     */
    public function reinstall()
    {
        $this->uninstall();
        $this->install();
    }
}