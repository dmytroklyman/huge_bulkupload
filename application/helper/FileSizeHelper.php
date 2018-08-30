<?php

/**
 * Helper that allows to get the max upload file size from the server
 *
 * @author Dmytro Klyman <klyman.dmytro@gmail.com>
 */
class FileSizeHelper
{
    private static $maxSize;

    /**
     * Calculate max upload file size dependent on php configuration
     *
     * @return int
     */
    public static function calculateMaxUploadFileSize(): int
    {
        if (self::$maxSize !== null) {
            return self::$maxSize;
        }

        $maxSize = -1;

        $configParams = [
            'post_max_size', 'upload_max_filesize', 'memory_limit'
        ];

        foreach ($configParams as $param) {
            $parmSize = self::parseSize(ini_get($param));

            if ($parmSize === 0) {
                continue;
            }

            if ($maxSize === -1 || $parmSize < $maxSize) {
                $maxSize = $parmSize;
            }
        }

        self::$maxSize = $maxSize;
        return self::$maxSize;
    }

    /**
     * Convert the size string into kilobyte int value.
     *
     * @param string $size  Size string
     * @return int
     */
    protected static function parseSize(string $size): int
    {
        $unit = preg_replace('/[^bkmg]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return (int)floor($size * pow(1024, stripos('bkmg', $unit[0])));
        }

        return (int)floor($size);
    }

}