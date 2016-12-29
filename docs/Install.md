# Install

Пример реализации скасса `InstallCommands`

```php

class InstallCommands extends AbstractCommand
{
    /**
     * @param null $dir
     * @return InstallerInterface[]
     */
    public static function getInstallers($dir = null)
    {
        if (!isset($dir)) {
            $dir = __DIR__;
        }
        return parent::getInstallers($dir);
    }
}
```

