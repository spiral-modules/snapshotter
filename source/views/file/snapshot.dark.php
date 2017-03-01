<?php
/**
 * @var \Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord $snapshot
 * @var \Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord $entity
 * @var \Spiral\Snapshotter\Helpers\Timestamps                         $timestamps
 * @var array                                                          $occurred
 * @var \Spiral\ORM\Entities\RecordSelector                            $selector
 */
?>
<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>

<define:scripts>
    <block:scripts/>
    <script>
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
            obj.contentWindow.document.body.style.padding = 0;
        }
    </script>
</define:scripts>

<define:styles>
    <define:styles/>
    <style>
        dl dt {
            width: 150px;
        }
    </style>
</define:styles>

<define:actions>
    <vault:guard permission="vault.snapshots.remove">
        <vault:uri target="snapshots:remove" icon="delete" class="btn red waves-effect waves-light"
                   options="<?= ['id' => $snapshot['filename'], 'backToList' => 1] ?>">
            [[Remove all]]
        </vault:uri>
    </vault:guard>
    <vault:uri target="snapshots" class="btn-flat teal-text waves-effect" post-icon="trending_flat">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <tab:wrapper>
        <tab:item id="last" title="[[Snapshot]]" icon="announcement">
            <vault:guard permission="vault.snapshots.view">
                <vault:block>
                    <iframe src="<?= vault()->uri('snapshots:iframe',
                        ['id' => $snapshot->primaryKey()]) ?>" width="100%" height="100%"
                            frameborder="0" scrolling="no"
                            onload="javascript:resizeIframe(this);"></iframe>
                </vault:block>
            </vault:guard>
        </tab:item>
    </tab:wrapper>
</define:content>