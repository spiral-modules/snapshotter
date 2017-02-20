<?php

namespace Spiral\Snapshotter\AggregationHandler\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Database\Builders\SelectQuery;
use Spiral\Http\Exceptions\ClientExceptions\ForbiddenException;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;
use Spiral\Snapshotter\Helpers\Names;
use Spiral\Snapshotter\Helpers\Timestamps;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Snapshotter\Database\SnapshotAggregation;
use Spiral\Snapshotter\Database\AggregatedSnapshot;
use Spiral\Snapshotter\Database\Sources\AggregationSource;
use Spiral\Snapshotter\Models\AggregationService;

use Spiral\Vault\Vault;
use Spiral\Views\ViewManager;

/**
 * @property InputManager    $input
 * @property ViewManager     $views
 * @property Vault           $vault
 * @property ResponseWrapper $response
 */
class SnapshotsController extends Controller
{
    use AuthorizesTrait, TranslatorTrait;

    const GUARD_NAMESPACE = 'vault.snapshots';

    /**
     * @param SnapshotSource $source
     * @param Timestamps     $timestamps
     * @param Names          $names
     * @return string
     */
    public function indexAction(SnapshotSource $source, Timestamps $timestamps, Names $names)
    {
        $selector = $source->findWithLast()->orderBy(
            'last_incident.time_created',
            SelectQuery::SORT_DESC
        );

        return $this->views->render('snapshotter:aggregation/list', [
            'selector'     => $selector,
            'lastSnapshot' => $source->findLast(),
            'timestamps'   => $timestamps,
            'names'        => $names
        ]);
    }

    /**
     * @param string          $id
     * @param SnapshotService $service
     * @param IncidentSource  $source
     * @param Timestamps      $timestamps
     * @param Names           $names
     * @return string
     */
    public function editAction(
        $id,
        SnapshotService $service,
        IncidentSource $source,
        Timestamps $timestamps,
        Names $names
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $service->getSource()->findByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('aggregation'));

        $source = $source->findBySnapshot($snapshot)->orderBy(
            'time_created',
            SelectQuery::SORT_DESC
        );

        return $this->views->render('snapshotter:aggregation/snapshot', [
            'source'     => $source,
            'snapshot'   => $snapshot,
            'timestamps' => $timestamps,
            'names'      => $names
        ]);
    }

    /**
     * @param string            $id
     * @param AggregationSource $source
     * @return array
     */
    public function suppressAction($id, AggregationSource $source)
    {
        /** @var SnapshotAggregation $aggregation */
        $aggregation = $source->findByPK($id);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('edit', compact('aggregation'));

        $aggregation->setSuppression($this->input->data('suppression', false));
        $aggregation->save();

        return [
            'status'  => 200,
            'message' => $this->say('Suppression status updated.')
        ];
    }

    /**
     * @param string         $id
     * @param SnapshotSource $source
     * @return mixed
     */
    public function snapshotAction($id, SnapshotSource $source)
    {
        $snapshot = $source->findByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $this->views->render('snapshotter:aggregation/incident', compact('snapshot'));
    }

    /**
     * @param string         $id
     * @param SnapshotSource $source
     * @return string
     */
    public function iframeAction($id, SnapshotSource $source)
    {
        /** @var AggregatedSnapshot $snapshot */
        $snapshot = $source->findByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        try {
            return file_get_contents($snapshot->filename);
        } catch (\Exception $exception) {
            throw new NotFoundException;
        }
    }

    /**
     * @param AggregationService $aggregationService
     * @param AggregationSource  $aggregationSource
     * @param SnapshotSource     $snapshotSource
     * @return array
     */
    public function removeAllAction(
        AggregationService $aggregationService,
        AggregationSource $aggregationSource,
        SnapshotSource $snapshotSource
    ) {
        $this->authorize('remove');

        /** @var SnapshotAggregation $aggregation */
        foreach ($aggregationSource->find() as $aggregation) {
            $countDeleted = 0;
            if (!empty($snapshotSource->findStored($aggregation)->count())) {
                foreach ($snapshotSource->findStored($aggregation) as $snapshot) {
                    $countDeleted++;
                    $snapshotSource->delete($snapshot);
                }
            }

            if (!empty($countDeleted)) {
                $aggregationService->deleteSnapshots($aggregation, $countDeleted);
                $aggregation->save();
            }
        }

        $uri = $this->vault->uri('snapshots');

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshot deleted.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }

    /**
     * @param string                 $id
     * @param AggregationService     $aggregationService
     * @param SnapshotSource         $snapshotSource
     * @param ServerRequestInterface $request
     * @return array
     */
    public function removeSnapshotsAction(
        $id,
        AggregationService $aggregationService,
        SnapshotSource $snapshotSource,
        ServerRequestInterface $request
    ) {
        /**
         * @var SnapshotAggregation $aggregation
         */
        $aggregation = $aggregationService->getSource()->findByPK($id);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('remove', compact('aggregation'));

        $countDeleted = 0;
        if (!empty($snapshotSource->findStored($aggregation)->count())) {
            foreach ($snapshotSource->findStored($aggregation) as $snapshot) {
                $countDeleted++;
                $snapshotSource->delete($snapshot);
            }
        }

        if (!empty($countDeleted)) {
            $aggregationService->deleteSnapshots($aggregation, $countDeleted);
            $aggregation->save();
        }

        $uri = $request->getServerParams()['HTTP_REFERER'];

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshots aggregation deleted.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }

    /**
     * @param string             $id
     * @param SnapshotSource     $snapshotSource
     * @param AggregationService $aggregationService
     * @return array
     */
    public function removeSnapshotAction(
        $id,
        SnapshotSource $snapshotSource,
        AggregationService $aggregationService
    ) {
        /**
         * @var SnapshotRecord  $snapshot
         * @var SnapshotAggregation $aggregation
         */
        $snapshot = $snapshotSource->findByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        if (!$snapshot->status->isStored()) {
            throw new ForbiddenException;
        }

        $aggregation = $aggregationService->getSource()->findBySnapshot($snapshot);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('remove', compact('aggregation', 'snapshot'));

        $aggregationService->deleteSnapshots($aggregation, 1);
        $aggregation->save();

        $snapshotSource->delete($snapshot);

        $uri = $this->vault->uri('snapshots:edit', ['id' => $aggregation->primaryKey()]);

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshot deleted.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }
}