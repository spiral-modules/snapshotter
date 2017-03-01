<?php
/**
 * @var array                                  $selector
 * @var \Spiral\Snapshotter\Helpers\Timestamps $timestamps
 * @var \Spiral\Snapshotter\Helpers\Names      $names
 */
?>
<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>

<define:actions>
    <vault:guard permission="vault.snapshots.removeAll">
        <?php if (count($selector)) { ?>
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
            <span title="<?= $timestamps->getTime($entity['timestamp'], true) ?>">
                <?= $timestamps->getTime($entity['timestamp']) ?>
            </span>
        </grid:cell>

        <grid:cell label="[[File:]]">
            <span title="<?= e($entity['path']) ?>">
                <?= e(\Spiral\Support\Strings::shorter($names->onlyName(['path']), 100)) ?>
            </span>
        </grid:cell>

        <grid:cell style="text-align:right">
            <vault:guard permission="vault.snapshots.view">
                <vault:uri target="snapshots:view" icon="edit"
                           options="<?= ['id' => $entity['filename']] ?>"
                           class="btn-flat waves-effect"/>
            </vault:guard>
        </grid:cell>
        <grid:cell style="text-align:right">
            <vault:guard permission="vault.snapshots.remove">
                <vault:uri target="snapshots:remove" icon="delete"
                           class="btn red waves-effect waves-light"
                           options="<?= ['id' => $entity['filename']] ?>"/>
            </vault:guard>
        </grid:cell>
    </vault:grid>
</define:content>
