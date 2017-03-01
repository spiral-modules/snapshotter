<extends:vault:layout title="[[Vault : Snapshot]]"
                      class="wide-content"/><!--Shared keeper elements-->
<dark:use bundle="vault:bundle"/>

<?php
/**
 * @var \Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord $incident
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
                       class="btn teal waves-effect  waves-light"
                       options="<?= ['id' => $incident->primaryKey()] ?>">[[Suppress]]
            </vault:uri>
        <?php } ?>

        <vault:uri target="snapshots:view" class="btn-flat teal-text waves-effect"
                   post-icon="trending_flat"
                   options="<?= ['id' => ''/*$incident->getSnapshot()->primaryKey()*/] ?>">
            [[BACK]]
        </vault:uri>
    </vault:guard>
</define:actions>

<define:content>
    <vault:guard permission="vault.snapshots.view">
        <vault:block>
            <iframe src="<?= vault()->uri('snapshots:iframeIncident',
                ['id' => $incident->primaryKey()]) ?>" width="100%" height="100%" frameborder="0"
                    scrolling="no" onload="javascript:resizeIframe(this);"></iframe>
        </vault:block>
    </vault:guard>
</define:content>