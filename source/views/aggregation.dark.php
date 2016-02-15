<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>
<?php #compile
/**
 * @var \Spiral\Snapshotter\Database\Aggregation $aggregation
 * @var \Spiral\Snapshotter\Database\Snapshot    $entity
 */
?>

<define:actions>
    <?php
    if (!empty($aggregation->count_stored)) {
        ?>
        <vault:uri target="snapshots:removeSnapshots" icon="delete"
                   class="btn red waves-effect waves-light"
                   options="<?= ['id' => $aggregation->id] ?>">
            [[Remove all]]
        </vault:uri>
        <?php
    }
    ?>
    <vault:uri target="snapshots" class="btn-flat teal-text waves-effect" post-icon="trending_flat">
        [[BACK]]
    </vault:uri>
</define:actions>

<define:content>
    <vault:card title="[[Last Snapshot:]]">
        <p><?= $aggregation->exception_teaser ?></p>
        <?php
        if (empty($aggregation->count_stored)) {
            ?>
            <p class="grey-text">[[No snapshots stored.]]</p>
            <?php
        } else {
            ?>
            <p class="grey-text"><?= $aggregation->whenLast() ?> (<?= $aggregation->whenLast(true) ?>)</p>
            <?php
        }
        ?>
    </vault:card>
    <div class="row">
        <div class="col s6 m6">
            <vault:block title="[[Snapshot suppression:]]">
                <?php
                $formAction = vault()->uri('snapshots:suppress', [
                    'id' => $aggregation->id
                ]);
                ?>
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
        <div class="col s2 m2">
            <vault:block title="[[Snapshots counters:]]">
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
            <vault:block title="[[Snapshot occurrance time:]]">
                <dl>
                    <?php
                    if ($aggregation->count_occurred->serializeData() > 1) {
                        ?>
                        <dt>[[First:]]</dt>
                        <dd>
                            <?= $aggregation->whenFirst() ?>
                            <span class="grey-text">(<?= $aggregation->whenFirst(true) ?>)</span>
                        </dd>
                        <?php
                    }
                    ?>
                    <dt>[[Last:]]</dt>
                    <dd>
                        <?= $aggregation->whenLast() ?>
                        <span class="grey-text">(<?= $aggregation->whenLast(true) ?>)</span>
                    </dd>
                </dl>
            </vault:block>
        </div>
    </div>
    <?php
    //todo graph
    ?>
    <vault:grid source="<?= $source ?>" as="entity" color="teal">
        <grid:cell label="[[ID:]]" value="<?= $entity->id ?>"/>

        <grid:cell label="[[Occurred:]]">
            <?= $entity->when() ?>
            <span class="grey-text">(<?= $entity->when(true) ?>)</span>
        </grid:cell>

        <grid:cell label="[[Status:]]" value="<?= e($entity->status) ?>"/>
        <grid:cell label="[[Class:]]" value="<?= e($entity->exception_classname) ?>"/>
        <grid:cell label="[[Filename:]]" value="<?= e(basename($entity->filename)) ?>"/>

        <grid:cell style="text-align:right">
            <?php
            if ($entity->stored()) {
                ?>
                <vault:uri target="snapshots:snapshot" icon="edit" class="btn-flat waves-effect"
                           options="<?= ['id' => $entity->id] ?>"/>
                <?php
            }
            ?>
        </grid:cell>

        <grid:cell style="text-align:right">
            <?php
            if ($entity->stored()) {
                ?>
                <vault:uri target="snapshots:removeSnapshot" icon="delete"
                           class="btn red waves-effect waves-light"
                           options="<?= ['id' => $entity->id] ?>">
                    [[Remove]]
                </vault:uri>
                <?php
            }
            ?>
        </grid:cell>
    </vault:grid>
</define:content>