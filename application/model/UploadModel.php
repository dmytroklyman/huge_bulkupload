<?php

/**
 * Class UploadModel
 *
 * @author Dmytro Klyman <klyman.dmytro@gmail.com>
 */
class UploadModel
{
    private const ROWS_PRO_INSERT = 100;

    private const CSV_MIME_TYPES = [
        'text/plain',
        'text/csv'
    ];

    /**
     * Generate the data from the uploaded file
     *
     * @param array $uploadFile                     Uploaded file
     * @param \JamesGordo\CSV\Parser|null $parser   Parser object
     * @return array
     */
    public static function generateData(array $uploadFile, ?\JamesGordo\CSV\Parser $parser = null): array
    {
        if ($parser === null) {
            $parser = new \JamesGordo\CSV\Parser();
        }

        $parser->setCsv($uploadFile['tmp_name']);
        $parser->setDelimeter(',');
        $parser->parse();
        return $parser->all();
    }

    /**
     * Checks if the upload folder exists and is writable
     *
     * @return bool
     */
    public static function isUploadFolderWritable(): bool
    {
        if (is_dir(Config::get('PATH_BULK_UPLOAD')) && is_writable(Config::get('PATH_BULK_UPLOAD'))) {
            return true;
        }

        Session::add('feedback_negative', Text::get('BULK_UPLOAD_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE'));
        return false;
    }

    /**
     * Validates the image
     * Only csv files are allowed
     *
     * @param array $file   File to validate
     * @return bool
     */
    public static function validateUploadFile(array $file): bool
    {
        // if input file too big (according to the php.ini config)
        if (!$file['size']) {
            Session::add('feedback_negative', Text::get('BULK_UPLOAD_EMPTY'));
            return false;
        }

        if ($file['size'] > FileSizeHelper::calculateMaxUploadFileSize()) {
            Session::add('feedback_negative', Text::get('BULK_UPLOAD_TOO_BIG', [
                'max_size' => FileSizeHelper::calculateMaxUploadFileSize(),
            ]));
            return false;
        }

        // Check mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::CSV_MIME_TYPES)) {
            Session::add('feedback_negative', Text::get('BULK_UPLOAD_WRONG_TYPE'));
            return false;
        }

        return true;
    }

    /**
     * Writes marker to database, saying user has an avatar now
     *
     * @param array $data       Data for insert
     * @param \PDO|null $db     DB object
     * @return bool
     */
    public static function insertDataToDatabase(array $data, ?\PDO $db = null): bool
    {
        if ($db === null) {
            $db = DatabaseFactory::getFactory()->getConnection();
        }

        // Spllit the data on portions. And generate the insert pro portion.
        $inserts = (int)ceil(count($data) / self::ROWS_PRO_INSERT);

        try {

            // Use transaction.
            $db->beginTransaction();

            for ($i = 0; $i < $inserts; ++$i) {

                $sql = [];

                $max_id = ($i + 1) * self::ROWS_PRO_INSERT;

                if (count($data) < $max_id) {
                    $max_id = count($data);
                }

                for ($j = $i * self::ROWS_PRO_INSERT; $j < $max_id; ++$j) {
                    $row = $data[$j];
                    $sql[] = '(' . (int)$row->id . ', ' . (int)$row->user_id . ', ' . $db->quote($row->name) . ', ' . $db->quote($row->value) . ')';
                }

                $query_str = 'INSERT INTO bulkupload (bulkupload_id, user_id, bulkupload_name, bulkupload_value)'
                    . ' VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE bulkupload_id = bulkupload_id';
                $query = $db->prepare($query_str);
                $query->execute();

            }

            $db->commit();

        } catch (\Exception $e) {
            // Something went wrong
            Session::add('feedback_negative', Text::get('BULK_UPLOAD_FAILED'));
            return false;
        }

        return true;
    }

    /**
     * Get all bulkupload rows
     *
     * @param \PDO|null $db     DB object
     * @return array
     */
    public static function getAllBulkuploads(?\PDO $db = null): array
    {
        if ($db === null) {
            $db = DatabaseFactory::getFactory()->getConnection();
        }

        $sql = 'SELECT bulkupload_id AS id, user_id, bulkupload_name AS text, bulkupload_value AS value'
            . ' FROM bulkupload ORDER BY user_id ASC';
        $query = $db->prepare($sql);
        $query->execute();

        // fetchAll() is the PDO method that gets all result rows
        return $query->fetchAll();
    }
}
