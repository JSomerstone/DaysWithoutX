<?php

abstract class FileHelper
{

    /**
     * Makes a directory into filesystem
     * @param string $path absolute path to create
     * @param int $permissions optional, 0777 by default
     * @throws FileHelperException if fails to create
     */
    public static function createDirectoryOrFail($path, $permissions = 0777)
    {
        if ( is_dir($path))
            return;

        if ( mkdir($path, $permissions, true) === false)
        {
            throw new FileHelperException(
                "Unalbe to create directory '$path"
            );
        }
    }

    /**
     * Recursively cleans content of given directory
     *
     * @param string $path absolute path to dir
     * @throws FileHelperException if unable
     */
    public static function cleanupDirectoryOrFail($path)
    {
        foreach (glob("'$path/*'") as $fileOrFolder)
        {
            if (is_dir($fileOrFolder))
            {
                self::createDirectoryOrFail($fileOrFolder);
            }
            else
            {
                echo '.';
                if ( unlink($fileOrFolder) === false)
                {
                    throw new FileHelperException(
                        "Unable to remove file '$fileOrFolder'"
                    );
                }
            }
        }
    }

}

class FileHelperException extends Exception
{

}