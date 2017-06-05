<extends:vault:layout title="[[Vault : Snapshot]]"
                      class="wide-content"/><!--Shared keeper elements-->
<dark:use bundle="vault:bundle"/>

<?php
/**
 * @var \Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord $incident
 * @var \Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord $snapshot
 */
?>
<define:scripts>
    <block:scripts/>
    <script>
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
            obj.contentWindow.document.body.style.padding = 0;
        }
    </script>
</define:scripts>

<define:actions>
    <vault:guard permission="vault.snapshots.edit">
        <?php if ($incident->status->isStored()) { ?>
            <vault:uri target="snapshots:suppressIncident"
                       class="btn blue-grey darken-2 waves-effect waves-light" icon="archive"
                       options="<?= [
                           'id'       => $snapshot->primaryKey(),
                           'incident' => $incident->primaryKey()
                       ] ?>">
                [[Suppress]]
            </vault:uri>
        <?php } ?>

        <vault:uri target="snapshots:view" class="btn-flat  waves-effect"
                   post-icon="trending_flat" options="<?= ['id' => $snapshot->primaryKey()] ?>">
            [[BACK]]
        </vault:uri>
    </vault:guard>
</define:actions>

<define:content>
    <vault:guard permission="vault.snapshots.view">
        <vault:block>
            <iframe src="<?= vault()->uri('snapshots:iframeIncident', [
                'id'       => $snapshot->primaryKey(),
                'incident' => $incident->primaryKey()
            ]) ?>" width="100%" height="100%" frameborder="0" scrolling="no"
                    onload="javascript:resizeIframe(this);"></iframe>
        </vault:block>
    </vault:guard>
</define:content>