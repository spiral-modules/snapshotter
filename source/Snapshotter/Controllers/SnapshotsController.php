<?php
namespace Spiral\Snapshotter\Controllers;

use Spiral\Core\Controller;
use Spiral\Http\Exceptions\ClientExceptions\ForbiddenException;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Security\Traits\AuthorizesTrait;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Snapshotter\Database\Aggregation;
use Spiral\Snapshotter\Database\Snapshot;
use Spiral\Snapshotter\Database\Sources\AggregationSource;
use Spiral\Snapshotter\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\Models\AggregationService;
use Spiral\Snapshotter\Models\Statistics;

/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 09.02.2016
 * Time: 17:47
 */
class SnapshotsController extends Controller
{
    use AuthorizesTrait, TranslatorTrait;

    const GUARD_NAMESPACE = 'keeper.vault.snapshots';

    /**
     * @param AggregationSource $source
     * @param Statistics        $statistics
     * @return mixed
     */
    public function indexAction(AggregationSource $source, Statistics $statistics)
    {
        //todo filter by active (has snapshots), all - NOW filter is hardcoded
        //todo graph
        return $this->views->render('snapshotter:list', [
            'source'       => $source->findWithSnapshots()->orderBy('last_occurred_time', 'DESC'),
            'lastSnapshot' => $source->findLast(),
            'statistics'   => $statistics
        ]);
    }

    /**
     * @param string             $id
     * @param AggregationService $aggregationService
     * @param SnapshotSource     $snapshotSource
     * @return mixed
     */
    public function editAction(
        $id,
        AggregationService $aggregationService,
        SnapshotSource $snapshotSource
    ) {
        //todo graph
        /**
         * @var Aggregation $aggregation
         */
        $aggregation = $aggregationService->getSource()->findByPK($id);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('aggregation'));

        return $this->views->render('snapshotter:aggregation', [
            'source'      => $snapshotSource->findStored($aggregation)->orderBy('id', 'DESC'),
            'aggregation' => $aggregation
        ]);
    }

    /**
     * @param string            $id
     * @param AggregationSource $source
     * @return array
     */
    public function suppressAction($id, AggregationSource $source)
    {
        /**
         * @var Aggregation $aggregation
         */
        $aggregation = $source->findByPK($id);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('edit', compact('aggregation'));

        $aggregation->setSuppression($this->input->data('suppression', false));
        $source->save($aggregation);

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

        return $this->views->render('snapshotter:snapshot', compact('snapshot'));
    }

    /**
     * @param string         $id
     * @param SnapshotSource $source
     * @return string
     */
    public function iframeAction($id, SnapshotSource $source)
    {
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
                $aggregationSource->save($aggregation);
            }
        }

        return [
            'status'  => 200,
            'message' => $this->say('Snapshot deleted.'),
            'action'  => [
                'redirect' => $this->vault->uri('snapshots')
            ]
        ];
    }

    /**
     * @param string             $id
     * @param AggregationService $aggregationService
     * @param SnapshotSource     $snapshotSource
     * @return array
     */
    public function removeSnapshotsAction(
        $id,
        AggregationService $aggregationService,
        SnapshotSource $snapshotSource
    ) {
        /**
         * @var Aggregation $aggregation
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
            $aggregationService->getSource()->save($aggregation);
        }

        return [
            'status'  => 200,
            'message' => $this->say('Snapshot deleted.'),
            'action'  => [
                'redirect' => $this->vault->uri('snapshots:edit', ['id' => $aggregation->id])
            ]
        ];
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
         * @var Snapshot    $snapshot
         * @var Aggregation $aggregation
         */
        $snapshot = $snapshotSource->findByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        if (!$snapshot->stored()) {
            throw new ForbiddenException;
        }

        $aggregation = $aggregationService->getSource()->findBySnapshot($snapshot);
        if (empty($aggregation)) {
            throw new NotFoundException;
        }

        $this->authorize('remove', compact('aggregation', 'snapshot'));

        $aggregationService->deleteSnapshots($aggregation, 1);
        $aggregationService->getSource()->save($aggregation);

        $snapshotSource->delete($snapshot);

        return [
            'status'  => 200,
            'message' => $this->say('Snapshot deleted.'),
            'action'  => [
                'redirect' => $this->vault->uri('snapshots:edit', ['id' => $aggregation->id])
            ]
        ];
    }
}