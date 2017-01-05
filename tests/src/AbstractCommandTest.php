<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 05.01.17
 * Time: 13:58
 */

namespace zaboy\test\installer;

use zaboy\installer\Install\AbstractCommand;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{

    public function test__publicDir()
    {
        $expectedPublicDir = realpath('public');
        $publicDir = AbstractCommand::getPublicDir();
        $this->assertEquals($expectedPublicDir, $publicDir);
    }
}
