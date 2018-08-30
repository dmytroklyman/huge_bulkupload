<div class="container">
    <h1>Bulk Upload</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Try it</h3>

        <form action="<?php echo Config::get('URL'); ?>bulkupload/upload_action" method="post" enctype="multipart/form-data">
            <label for="csv_import">Select the csv file for import:</label>
            <input type="file" name="csv_import" required />
            <!-- max size 5 MB (as many people directly upload high res pictures from their digital cameras) -->
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $this->max_file_size ?>" />
            <input type="submit" value="Upload image" />
        </form>
    </div>
</div>
