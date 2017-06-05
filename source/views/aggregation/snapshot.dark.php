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

<!--Shared keeper elements-->
<dark:use bundle="vault:bundle"/>

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
        <?php if ($selector->count()) { ?>
            <vault:uri target="snapshots:remove" icon="delete"
                       class="btn red waves-effect waves-light"
                       options="<?= ['id' => $snapshot->primaryKey(), 'backToList' => 1] ?>">
                [[Remove]]
            </vault:uri>
        <?php } ?>
    </vault:guard>
    <vault:uri target="snapshots" class="btn-flat  waves-effect" post-icon="trending_flat">
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

        <tab:item id="caption" title="[[Information]]" icon="timeline">
            <div class="row">
                <div class="col s12 m4">
                    <vault:block title="[[Statistics:]]">
                        <dl>
                            <dt>[[First occurrence:]]</dt>
                            <dd>
                                <?= $timestamps->getTime($snapshot->time_created) ?>
                                <span class="grey-text">
                                    (<?= $timestamps->getTime($snapshot->time_created, true) ?>)
                                </span>
                            </dd>
                            <dt>[[Last occurrence:]]</dt>
                            <?php if ($snapshot->count_occurred > 1) { ?>
                                <dd>
                                    <?= $timestamps->getTime(
                                        $snapshot->getLastIncident()->time_created
                                    ) ?>
                                    <span class="grey-text">
                                        (<?= $timestamps->getTime(
                                            $snapshot->getLastIncident()->time_created,
                                            true
                                        ) ?>)
                                    </span>
                                </dd>
                            <?php } ?>

                            <dt>[[Total occurred:]]</dt>
                            <dd><?= $snapshot->count_occurred ?></dd>

                            <dt title="[[Daily / Weekly / Monthly / Yearly]]">[[D / W / M / Y:]]</dt>
                            <dd>
                                <?= join(' / ', $occurred) ?>
                            </dd>
                        </dl>
                    </vault:block>
                    <vault:block title="[[Suppression:]]">
                        <?php if (spiral(\Spiral\Security\GuardInterface::class)->allows('vault.snapshots.edit')) {
                            $formAction = vault()->uri('snapshots:suppress', [
                                'id' => $snapshot->primaryKey()
                            ]); ?>
                            <spiral:form action="<?= $formAction ?>">
                                <input type="checkbox" name="suppression" value="1" id="suppression"
                                    <?= $snapshot->isSuppressionEnabled() ? 'checked' : '' ?>/>
                                <label for="suppression">[[Enable]]</label>
                                <p class="grey-text">[[When enabled, snapshot incident history records will be created without source stored.]]</p>
                                <div class="right-align">
                                    <input type="submit" value="[[Update]]"
                                           class="btn  waves-effect"/>
                                </div>
                            </spiral:form>
                        <?php } else {
                            $suppressionState = $snapshot->isSuppressionEnabled();
                            ?>
                            <spiral:form>
                                <input type="checkbox" <?= $suppressionState ? 'checked' : '' ?>/>
                                <label>
                                    <?= $suppressionState ? '[[Enable]]' : '[[Disabled]]' ?>
                                </label>
                                <p class="grey-text">[[When enabled, snapshot incident history records will be created without source stored.]]</p>
                            </spiral:form>
                        <?php } ?>
                    </vault:block>
                </div>
                <div class="col s12 m8">
                    <vault:block title="[[Graphs:]]">
                        <div id="chart_div">

                        </div>
                    </vault:block>
                </div>
            </div>
        </tab:item>

        <tab:item id="history" title="[[History]]" icon="list">
            <?php if (!$selector->count()) { ?>
                <p class="card-panel-title">[[You have only
                    <a href="#last">one</a> snapshot occurred.]]</p>
            <?php } else { ?>
                <vault:grid source="<?= $selector ?>" as="entity" color="">
                    <grid:cell label="[[ID:]]" value="<?= $entity->primaryKey() ?>"/>

                    <grid:cell label="[[Occurred:]]">
                        <?= $timestamps->getTime($entity->time_created) ?>
                        <span class="grey-text">
                            (<?= $timestamps->getTime($entity->time_created, true) ?>)
                        </span>
                    </grid:cell>

                    <grid:cell label="[[Status:]]" value="<?= e($entity->status) ?>"/>
                    <grid:cell label="[[Code:]]" value="<?= e($entity->getExceptionCode()) ?>"/>

                    <grid:cell style="text-align:right">
                        <?php if ($entity->status->isStored()) { ?>
                            <vault:guard permission="vault.snapshots.view">
                                <vault:uri target="snapshots:incident" icon="import_contacts"
                                           title="[[View incident]]"
                                           class="btn waves-effect  text-left" options="<?= [
                                    'id'       => $snapshot->primaryKey(),
                                    'incident' => $entity->primaryKey(),
                                ] ?>"/>
                            </vault:guard>
                            <vault:guard permission="vault.snapshots.edit">
                                <vault:uri target="snapshots:suppressIncident" icon="archive"
                                           title="[[Suppress incident]]"
                                           class="btn blue-grey darken-2 waves-effect waves-light"
                                           options="<?= [
                                               'id'       => $snapshot->primaryKey(),
                                               'incident' => $entity->primaryKey(),
                                           ] ?>"></vault:uri>
                            </vault:guard>
                        <?php }

                        if (!$entity->status->isDeleted()) { ?>
                            <vault:guard permission="vault.snapshots.edit">
                                <vault:uri target="snapshots:removeIncident" icon="delete"
                                           title="[[Remove incident]]"
                                           class="btn red waves-effect waves-light" options="<?= [
                                    'id'       => $snapshot->primaryKey(),
                                    'incident' => $entity->primaryKey(),
                                ] ?>"/>
                            </vault:guard>
                        <?php } ?>
                    </grid:cell>
                </vault:grid>
            <?php } ?>
        </tab:item>
    </tab:wrapper>
</define:content>