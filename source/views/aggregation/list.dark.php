<?php
/**
 * @var \Spiral\ORM\Entities\RecordSelector                            $selector
 * @var \Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord $entity
 * @var \Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord $lastSnapshot
 * @var \Spiral\Snapshotter\Helpers\Timestamps                         $timestamps
 * @var \Spiral\Snapshotter\Helpers\Names                              $names
 */
?>
<extends:vault:layout title="[[Vault : Snapshots]]"
                      class="wide-content"/><!--Shared keeper elements-->
<dark:use bundle="vault:bundle"/>

<define:actions>
    <vault:guard permission="vault.snapshots.removeAll">
        <?php if ($selector->count()) { ?>
            <vault:uri target="snapshots:removeAll" icon="delete"
                       class="btn red waves-effect waves-light">
                [[Remove all]]
            </vault:uri>
        <?php } ?>
    </vault:guard>
</define:actions>

<define:content>
    <vault:card title="[[Statistics:]]">
    </vault:card>

    <?php if (!empty($lastSnapshot)) { ?>
        <vault:card title="[[Last Snapshot:]]">
            <div class="row">
                <div class="col s12 m10">
                    <p><?= $lastSnapshot->last_incident->getExceptionTeaser() ?></p>
                    <p class="grey-text"><?= $timestamps->getTime($lastSnapshot->time_created) ?>
                        (<?= $timestamps->getTime($lastSnapshot->time_created, true) ?>)</p>
                </div>
                <div class="col s12 m2 right-align">
                    <vault:guard permission="vault.snapshots.view">
                        <vault:uri target="snapshots:view" icon="edit"
                                   class="btn teal waves-effect waves-light"
                                   options="<?= ['id' => $lastSnapshot->primaryKey()] ?>">
                            [[View last]]
                        </vault:uri>
                    </vault:guard>
                </div>
            </div>
        </vault:card>
    <?php } ?>

    <vault:grid source="<?= $selector ?>" as="entity" color="teal">
        <grid:cell label="[[ID:]]" value="<?= $entity->id ?>"/>
        <grid:cell label="[[Last occurred:]]">
            <span title="<?= $timestamps->getTime(
                $entity->getLastIncident()->time_created,
                true
            ) ?>">
                <?= $timestamps->getTime($entity->getLastIncident()->time_created) ?>
            </span>
        </grid:cell>
        <grid:cell label="[[Message:]]">
            <span title="<?= e($entity->last_incident->getExceptionMessage()) ?>">
                <?= e(\Spiral\Support\Strings::shorter(
                    $entity->last_incident->getExceptionMessage(),
                    40
                )) ?>
            </span>
        </grid:cell>
        <grid:cell label="[[Class:]]">
            <span title="<?= e($entity->last_incident->getExceptionClass()) ?>">
                <?= e($names->onlyName($entity->last_incident->getExceptionClass())) ?>
            </span>
        </grid:cell>
        <grid:cell label="[[File:]]">
            <span title="<?= e($entity->last_incident->getExceptionFile()) ?>">
                <?= e(\Spiral\Support\Strings::shorter(
                    $names->onlyName($entity->last_incident->getExceptionFile()),
                    40
                )) ?>
            </span>
        </grid:cell>
        <grid:cell label="[[Code:]]">
            <span>
                <?= $entity->last_incident->getExceptionCode() ?>
            </span>
        </grid:cell>
        <grid:cell label="[[Line:]]">
            <span>
                <?= $entity->last_incident->getExceptionLine() ?>
            </span>
        </grid:cell>
        <grid:cell label="[[Occurred:]]" value="<?= $entity->count_occurred ?>"/>
        <grid:cell label="[[Suppress:]]">
            <b>
                <?= $entity->isSuppressionEnabled() ? '[[YES]]' : '[[NO]]' ?>
            </b>
        </grid:cell>

        <grid:cell style="text-align:right">
            <vault:guard permission="vault.snapshots.view">
                <vault:uri target="snapshots:view" icon="edit"
                           options="<?= ['id' => $entity->primaryKey()] ?>"
                           class="btn-flat waves-effect"/>
            </vault:guard>
        </grid:cell>
        <grid:cell style="text-align:right">
            <vault:guard permission="vault.snapshots.remove">
                <vault:uri target="snapshots:remove" icon="delete"
                           class="btn red waves-effect waves-light"
                           options="<?= ['id' => $entity->primaryKey()] ?>"/>
            </vault:guard>
        </grid:cell>
    </vault:grid>
</define:content>
