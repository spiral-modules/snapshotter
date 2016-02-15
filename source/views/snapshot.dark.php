<extends:vault:layout title="[[Vault : Snapshot]]" class="wide-content"/>
<?php
/**
 * @var \Vault\Snapshotter\Database\Snapshot $snapshot
 */
?>
<define:resources>
    <block:resources/>
    <script language="javascript" type="text/javascript">
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>
</define:resources>

<define:actions>
    <?php
    if ($snapshot->stored()) {
        ?>
        <vault:uri target="snapshots:removeSnapshot" icon="delete"
                   class="btn red waves-effect waves-light"
                   options="<?= ['id' => $snapshot->id] ?>">
            [[Remove]]
        </vault:uri>
        <?php
    }
    ?>

    <vault:uri target="snapshots:edit" class="btn-flat teal-text waves-effect"
               post-icon="trending_flat" options="<?= ['id' => $snapshot->aggregation_id] ?>">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <vault:card title="<?= e(basename($snapshot->filename)) ?>">
        <p class="grey-text"><?= $snapshot->when() ?> (<?= $snapshot->when(true) ?>)</p>
        <p><?= $snapshot->exception_classname ?></p>
        <p><?= $snapshot->filesize(true) ?></p>
    </vault:card>
    <?php
    switch ($snapshot->status) {
        case 'suppressed':
            ?>
            <h4>[[Can't render snapshot - it was suppressed.]]</h4>
            <?php
            break;
        case 'deleted':
            ?>
            <h4>[[Can't render snapshot - it was removed.]]</h4>
            <?php
            break;
        default:
            ?>
            <iframe src="<?= vault()->uri('snapshots:iframe', ['id' => $snapshot->id]) ?>"
                    width="100%" height="100%" frameborder="0" scrolling="no"
                    onload="javascript:resizeIframe(this);"></iframe>
            <?php
    }
    ?>
</define:content>