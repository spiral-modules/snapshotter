<?php
/**
 * @var \Spiral\Snapshotter\Database\SnapshotAggregation $aggregation
 * @var \Spiral\Snapshotter\Database\AggregatedSnapshot  $entity
 * @var \Spiral\Snapshotter\Database\AggregatedSnapshot  $snapshot
 * @var \Spiral\Snapshotter\Models\Timestamps            $timestamps
 */
?>
<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>

<define:resources>
    <block:resources/>
    <script language="javascript" type="text/javascript">
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>
</define:resources>

<define:actions>
    <?php if (!empty($aggregation->count_stored)) { ?>
        <vault:uri target="snapshots:removeSnapshots" icon="delete"
                   class="btn red waves-effect waves-light"
                   options="<?= ['id' => $aggregation->primaryKey()] ?>">
            [[Remove all]]
        </vault:uri>
    <?php } ?>
    <vault:uri target="snapshots" class="btn-flat teal-text waves-effect" post-icon="trending_flat">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <?php
    $timestamps = spiral(\Spiral\Snapshotter\Models\Timestamps::class);
    ?>
    <vault:card title="[[Last Snapshot:]]">
        <p><?= $aggregation->exception_teaser ?></p>
        <?php if (empty($aggregation->count_stored)) { ?>
            <p class="grey-text">[[No snapshots stored.]]</p>
        <?php } else { ?>
            <p class="grey-text"><?= $timestamps->lastOccurred($aggregation) ?>
                (<?= $timestamps->lastOccurred($aggregation, true) ?>)</p>
            <?php if (!empty($snapshot)) { ?>
                <p>[[You have only one snapshot occurred.]]</p>
            <?php }
        } ?>
    </vault:card>
    <div class="row">
        <div class="col s4 m4">
            <vault:block title="[[Suppression:]]">
                <?php $formAction = vault()->uri('snapshots:suppress', [
                    'id' => $aggregation->primaryKey()
                ]); ?>
                <spiral:form action="<?= $formAction ?>">
                    <p>
                        <input type="checkbox" name="suppression" value="1" id="suppression"
                            <?= $aggregation->isSuppressionEnabled() ? 'checked' : '' ?>/>
                        <label for="suppression">[[Enable]]</label>
                    <p class="grey-text">[[When enabled, snapshot duplicate files will not be
                        created, only <b>occurred counter</b> will be increased.]]</p>
                    <div class="right-align">
                        <input type="submit" value="[[Update]]" class="btn teal waves-effect"/>
                    </div>
                </spiral:form>
            </vault:block>
        </div>
        <div class="col s4 m4">
            <vault:block title="[[Counters:]]">
                <dl>
                    <dt>[[Occurred:]]</dt>
                    <dd><?= $aggregation->count_occurred ?></dd>

                    <dt>[[Suppressed:]]</dt>
                    <dd><?= $aggregation->count_suppressed ?></dd>

                    <dt>[[Removed:]]</dt>
                    <dd><?= $aggregation->count_deleted ?></dd>
                </dl>
            </vault:block>
        </div>
        <div class="col s4 m4">
            <vault:block title="[[Occurred time:]]">
                <dl>
                    <?php if ($aggregation->count_occurred > 1) { ?>
                        <dt>[[First:]]</dt>
                        <dd>
                            <?= $timestamps->firstOccurred($aggregation) ?>
                            <span class="grey-text">(<?= $timestamps->firstOccurred($aggregation,
                                    true) ?>)</span>
                        </dd>
                    <?php } ?>
                    <dt>[[Last:]]</dt>
                    <dd>
                        <?= $timestamps->firstOccurred($aggregation) ?>
                        <span class="grey-text">(<?= $timestamps->lastOccurred($aggregation,
                                true) ?>)</span>
                    </dd>
                </dl>
            </vault:block>
        </div>
    </div>

    <?php if (!empty($snapshot)) { ?>
        <p class="card-panel-title">[[You have only one snapshot occurred.]]</p>
        <iframe src="<?= vault()->uri('snapshots:iframe', ['id' => $snapshot->primaryKey()]) ?>" width="100%"
                height="100%" frameborder="0" scrolling="no"
                onload="javascript:resizeIframe(this);"></iframe>
    <?php } else { ?>
        <vault:grid source="<?= $source ?>" as="entity" color="teal">
            <grid:cell label="[[ID:]]" value="<?= $entity->primaryKey() ?>"/>

            <grid:cell label="[[Occurred:]]">
                <?= $timestamps->timeOccurred($entity) ?>
                <span class="grey-text">(<?= $timestamps->timeOccurred($entity, true) ?>)</span>
            </grid:cell>

            <grid:cell label="[[Status:]]" value="<?= e($entity->status) ?>"/>
            <grid:cell label="[[Class:]]" value="<?= e($entity->exception_classname) ?>"/>
            <grid:cell label="[[Filename:]]" value="<?= e(basename($entity->filename)) ?>"/>

            <grid:cell style="text-align:right">
                <?php if ($entity->isStored()) { ?>
                    <vault:uri target="snapshots:snapshot" icon="edit" class="btn-flat waves-effect"
                               options="<?= ['id' => $entity->primaryKey()] ?>"/>
                <?php } ?>
            </grid:cell>

            <grid:cell style="text-align:right">
                <?php if ($entity->isStored()) { ?>
                    <vault:uri target="snapshots:removeSnapshot" icon="delete"
                               class="btn red waves-effect waves-light"
                               options="<?= ['id' => $entity->primaryKey()] ?>">
                        [[Remove]]
                    </vault:uri>
                <?php } ?>
            </grid:cell>
        </vault:grid>
    <?php } ?>
</define:content>