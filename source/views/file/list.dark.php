<?php
/**
 * @var \Spiral\Snapshotter\FileHandler\Sources\ArraySource   $selector
 * @var \Spiral\Snapshotter\FileHandler\Entities\FileSnapshot $entity
 * @var \Spiral\Snapshotter\Helpers\Timestamps                $timestamps
 */
?>
<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>

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
    <vault:grid source="<?= $selector ?>" as="entity" color="teal">
        <grid:cell label="[[Last occurred:]]">
            <span>
                <?= $timestamps->getTime($entity->timestamp()) ?>
                (<span class="grey-text">
                    <?= $timestamps->getTime($entity->timestamp(), true) ?>
                </span>)
            </span>
        </grid:cell>

        <grid:cell label="[[File:]]">
            <span title="<?= e($entity->path()) ?>">
                <?= e(\Spiral\Support\Strings::shorter($entity->id(), 100)) ?>
            </span>
        </grid:cell>

        <grid:cell label="[[Size:]]">
            <?= e(\Spiral\Support\Strings::bytes($entity->size())) ?>
        </grid:cell>

        <grid:cell style="text-align:right">
            <vault:guard permission="vault.snapshots.view">
                <vault:uri target="snapshots:view" icon="edit"
                           options="<?= ['filename' => $entity->id()] ?>"
                           class="btn-flat waves-effect"/>
            </vault:guard>
            <vault:guard permission="vault.snapshots.remove">
                <vault:uri target="snapshots:remove" icon="delete"
                           class="btn red waves-effect waves-light"
                           options="<?= ['filename' => $entity->id()] ?>"/>
            </vault:guard>
        </grid:cell>
    </vault:grid>
</define:content>
