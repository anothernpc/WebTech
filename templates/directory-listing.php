<div class="breadcrumb">
    Current Path: <span><?php echo htmlspecialchars($path); ?></span>
</div>

<div class="file-list">
    <div class="directory">
        <a href="?path=<?php echo urlencode($parentPath); ?>">[DIR] ..</a>
    </div>

    <?php foreach ($directories as $directory): ?>
        <div class="directory">
            <a href="?path=<?php echo urlencode($path . '/' . $directory); ?>">[DIR] <?php echo htmlspecialchars($directory); ?></a>
        </div>
    <?php endforeach; ?>

    <?php foreach ($files as $file): ?>
        <div class="file">
            <a href="/admin/file/preview?path=<?php echo urlencode($path . '/' . $file); ?>">
                <?php echo htmlspecialchars($file); ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>