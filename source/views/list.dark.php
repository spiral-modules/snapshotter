<extends:vault:layout title="[[Vault : Snapshots]]" class="wide-content"/>
<?php
/**
 * @var \Vault\Snapshotter\Database\Aggregation $entity
 * @var \Vault\Snapshotter\Database\Aggregation $lastSnapshot
 */
?>

<define:actions>
    <?php
    if (!empty($lastSnapshot)) {
        ?>
        <vault:uri target="snapshots:removeAll" icon="delete"
                   class="btn red waves-effect waves-light">
            [[Remove all]]
        </vault:uri>

        <vault:uri target="snapshots:edit" icon="edit" class="btn teal waves-effect waves-light"
                   options="<?= ['id' => $lastSnapshot->id] ?>">
            [[View last]]
        </vault:uri>
        <?php
    }
    ?>
</define:actions>

<define:content>
    <vault:card title="[[Last Snapshot:]]">
        <?php
        if (empty($lastSnapshot)) {
            ?>
            <p class="grey-text">[[No snapshots occurred.]]</p>
            <?php
        } else {
            ?>
            <p class="grey-text"><?= $lastSnapshot->whenLast() ?> (<?= $lastSnapshot->whenLast(true) ?>)</p>
            <p><?= $lastSnapshot->exception_teaser ?></p>
            <?php
        }
        ?>
    </vault:card>
    <?php
    //todo graph
    ?>
    <vault:grid source="<?= $source ?>" as="entity" color="teal">
        <grid:cell label="[[ID:]]" value="<?= $entity->id ?>"/>
        <grid:cell label="[[Last occurred:]]">
            <?= $entity->whenLast() ?>
            <span class="grey-text">(<?= $entity->whenLast(true) ?>)</span>
        </grid:cell>
        <grid:cell label="[[Message:]]">
            <span title="<?= e($entity->exception_teaser) ?>">
                <?= e(\Spiral\Support\Strings::shorter($entity->exception_teaser, 100)) ?>
            </span>
        </grid:cell>
        <grid:cell label="[[All:]]" value="<?= $entity->count_occurred ?>"/>
        <grid:cell label="[[Stored:]]" value="<?= $entity->count_stored ?>"/>
        <grid:cell label="[[Suppression:]]">
            <b>
                <?= $entity->isSuppressionEnabled() ? '[[YES]]' : '[[NO]]' ?>
            </b>
            <?php
            if ($entity->isSuppressionEnabled()) {
                echo '(' . $entity->count_suppressed . ')';
            }
            ?>
        </grid:cell>

        <grid:cell style="text-align:right">
            <vault:uri target="snapshots:edit" icon="edit" options="<?= ['id' => $entity->id] ?>"
                       class="btn-flat waves-effect"/>
        </grid:cell>
        <grid:cell style="text-align:right">
            <vault:uri target="snapshots:removeSnapshots" icon="delete"
                       class="btn red waves-effect waves-light"
                       options="<?= ['id' => $entity->id] ?>"></vault:uri>
        </grid:cell>
    </vault:grid>
</define:content>
