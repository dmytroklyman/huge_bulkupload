<?php

/**
 * Controller for the bulk file upload
 *
 * @author Dmytro Klyman <klyman.dmytro@gmail.com>
 */
class BulkuploadController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handles what happens when user moves to URL/bulkupload/index - or - as this is the default controller, also
     * when user moves to /index or enter your application at base level
     *
     * @return void
     */
    public function index()
    {
        $this->View->render('bulkupload/index', [
            'max_file_size' => FileSizeHelper::calculateMaxUploadFileSize(),
        ]);
    }

    /**
     * This method displays the content of bulkupload table
     *
     * @return void
     */
    public function list()
    {
        $this->View->render('bulkupload/list', array(
            'data' => UploadModel::getAllBulkuploads()
        ));
    }

    /**
     * Perform the upload of the bulk import
     * POST-request
     *
     * @return void
     */
    public function upload_action()
    {
        if (!isset($_FILES['csv_import'])) {
            Session::add('feedback_negative', Text::get('BULK_UPLOAD_FAILED'));
            Redirect::to('bulkupload/index');
        }

        $uploaded_file = $_FILES['csv_import'];

        // It is always a good idea to use the DI
        if (!UploadModel::isUploadFolderWritable() || !UploadModel::validateUploadFile($uploaded_file)) {
            Redirect::to('bulkupload/index');
        }

        $parser = new \JamesGordo\CSV\Parser();
        $data = UploadModel::generateData($uploaded_file, $parser);

        if (!UploadModel::insertDataToDatabase($data)) {
            Redirect::to('bulkupload/index');
        }

        Session::add('feedback_positive', Text::get('BULK_UPLOAD_SUCCESSFUL'));
        Redirect::to('bulkupload/list');
    }
}
