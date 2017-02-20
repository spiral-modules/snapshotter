<extends:vault:layout title="[[Vault : Snapshot]]" class="wide-content"/>
<?php
/**
 * @var \Spiral\Snapshotter\Database\AggregatedSnapshot $snapshot
 * @var \Spiral\Snapshotter\Models\Timestamps           $timestamps
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
    <?php if ($snapshot->status->isStored()) { ?>
        <vault:uri target="snapshots:removeSnapshot" icon="delete"
                   class="btn red waves-effect waves-light"
                   options="<?= ['id' => $snapshot->primaryKey()] ?>">
            [[Remove]]
        </vault:uri>
    <?php } ?>

    <vault:uri target="snapshots:edit" class="btn-flat teal-text waves-effect"
               post-icon="trending_flat"
               options="<?= ['id' => $snapshot->aggregation->primaryKey()] ?>">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <?php
    $timestamps = spiral(\Spiral\Snapshotter\Models\Timestamps::class);
    ?>
    <vault:card title="<?= e(basename($snapshot->filename)) ?>">
        <p class="grey-text"><?= $timestamps->timeOccurred($snapshot) ?>
            (<?= $timestamps->timeOccurred($snapshot, true) ?>)</p>
        <p><?= $snapshot->exception_classname ?></p>
        <p><?= $snapshot->filesize(true) ?></p>
    </vault:card>
    <?php switch ($snapshot->status) {
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
            <iframe src="<?= vault()->uri('snapshots:iframe', ['id' => $snapshot->primaryKey()]) ?>"
                    width="100%" height="100%" frameborder="0" scrolling="no"
                    onload="javascript:resizeIframe(this);"></iframe>
        <?php } ?>
</define:content>