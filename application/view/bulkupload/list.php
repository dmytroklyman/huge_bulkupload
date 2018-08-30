<div class="container">
    <h1>Bulk Upload Data</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Existing data</h3>

        <?php if ($this->data): ?>
        <table class="note-table">
            <thead>
            <tr>
                <td>ID</td>
                <td>User ID</td>
                <td>Name</td>
                <td>Value</td>
            </tr>
            </thead>
            <tbody>
            <?php for ($i = 0, $n = count($this->data); $i < $n; ++$i): ?>
                <tr>
                    <td><?= $this->data[$i]->id; ?></td>
                    <td><?= $this->data[$i]->user_id; ?></td>
                    <td><?= htmlentities($this->data[$i]->text); ?></td>
                    <td><?= htmlentities($this->data[$i]->value); ?></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
