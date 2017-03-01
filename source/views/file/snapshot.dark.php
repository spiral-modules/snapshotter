<?php
/**
 * @var \Spiral\Snapshotter\FileHandler\Entities\FileSnapshot $snapshot
 * @var \Spiral\Snapshotter\Helpers\Timestamps                $timestamps
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
                   options="<?= ['filename' => $snapshot->id(), 'backToList' => 1] ?>">
            [[Remove]]
        </vault:uri>
    </vault:guard>
    <vault:uri target="snapshots" class="btn-flat teal-text waves-effect" post-icon="trending_flat">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <vault:guard permission="vault.snapshots.view">
        <vault:card>
            <p><?= $snapshot->id() ?></p>
            <p class="grey-text"><?= $timestamps->getTime($snapshot->timestamp()) ?>
                (<?= $timestamps->getTime($snapshot->timestamp(), true) ?>)</p>
        </vault:card>
        <vault:block>
            <iframe src="<?= vault()->uri('snapshots:iframe',
                ['filename' => $snapshot->id()]) ?>" width="100%" height="100%" frameborder="0"
                    scrolling="no" onload="javascript:resizeIframe(this);"></iframe>
        </vault:block>
    </vault:guard>
</define:content>