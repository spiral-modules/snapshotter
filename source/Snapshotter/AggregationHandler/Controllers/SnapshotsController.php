<?php

namespace Spiral\Snapshotter\AggregationHandler\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Database\Builders\SelectQuery;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;
use Spiral\Snapshotter\Helpers\Names;
use Spiral\Snapshotter\Helpers\Timestamps;
use Spiral\Translator\Traits\TranslatorTrait;

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
     * List of snapshots.
     *
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
     * View snapshot.
     *
     * @param string|int      $id
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
        $snapshot = $service->getSource()->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        $selector = $source->findSnapshotHistory($snapshot)->orderBy(
            'time_created',
            SelectQuery::SORT_DESC
        );

        $occurred = $service->countOccurred($snapshot, $source);

        return $this->views->render('snapshotter:aggregation/snapshot', [
            'selector'   => $selector,
            'occurred'   => $occurred,
            'snapshot'   => $snapshot,
            'timestamps' => $timestamps,
            'names'      => $names
        ]);
    }

    /**
     * Suppression snapshot state.
     *
     * @param string|int     $id
     * @param SnapshotSource $source
     * @return array
     */
    public function suppressAction($id, SnapshotSource $source)
    {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $source->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('edit', compact('snapshot'));

        $snapshot->setSuppression($this->input->data('suppression', false));
        $snapshot->save();

        return [
            'status'  => 200,
            'message' => $this->say('Suppression status updated.')
        ];
    }

    /**
     * View last snapshot incident source.
     *
     * @param string|int     $id
     * @param SnapshotSource $source
     * @return string
     */
    public function iframeAction($id, SnapshotSource $source)
    {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $source->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $snapshot->getLastIncident()->getExceptionSource();
    }

    /**
     * Remove all snapshots with all incident records.
     *
     * @param SnapshotService $service
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    public function removeAllAction(SnapshotService $service)
    {
        $this->authorize('removeAll');

        foreach ($service->getSource()->findWithLast() as $snapshot) {
            $service->delete($snapshot);
        }

        $uri = $this->vault->uri('snapshots');

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshots deleted.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }

    /**
     * Remove single snapshot with all incident records.
     *
     * @param string|int             $id
     * @param SnapshotService        $service
     * @param ServerRequestInterface $request
     * @return array
     */
    public function removeAction(
        $id,
        SnapshotService $service,
        ServerRequestInterface $request
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $service->getSource()->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('remove', compact('snapshot'));

        $service->delete($snapshot);

        $uri = $this->removeBackURI($request);

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
     * Build redirect URI for removal operation.
     *
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\UriInterface
     */
    protected function removeBackURI(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();
        if (array_key_exists('backToList', $query)) {
            $uri = $this->vault->uri('snapshots');
        } else {
            $uri = $request->getServerParams()['HTTP_REFERER'];
        }

        return $uri;
    }

    /**
     * Suppress snapshot incident. Can suppress only stored snapshots.
     *
     * @param string|int     $id
     * @param IncidentSource $incidentSource
     * @param SnapshotSource $snapshotSource
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function suppressIncidentAction(
        $id,
        IncidentSource $incidentSource,
        SnapshotSource $snapshotSource
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $snapshotSource->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        /** @var IncidentRecord $incident */
        $incident = $incidentSource->findStoredBySnapshotByPK(
            $snapshot,
            $this->input->query('incident')
        );
        if (empty($incident)) {
            throw new NotFoundException;
        }

        $this->authorize('edit', compact('snapshot'));

        $incident->suppress();
        $incident->save();

        $uri = $this->vault
            ->uri('snapshots:edit', ['id' => $snapshot->primaryKey()])
            ->withFragment('history');

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshot incident suppressed.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }

    /**
     * View snapshot incident.
     *
     * @param string|int     $id
     * @param IncidentSource $incidentSource
     * @param SnapshotSource $snapshotSource
     * @return string
     */
    public function incidentAction(
        $id,
        IncidentSource $incidentSource,
        SnapshotSource $snapshotSource
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $snapshotSource->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        /** @var IncidentRecord $incident */
        $incident = $incidentSource->findStoredBySnapshotByPK(
            $snapshot,
            $this->input->query('incident')
        );
        if (empty($incident)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $this->views->render('snapshotter:aggregation/incident', compact('incident'));
    }

    /**
     * View snapshot incident source.
     *
     * @param string|int     $id
     * @param IncidentSource $incidentSource
     * @param SnapshotSource $snapshotSource
     * @return null|string
     */
    public function iframeIncidentAction(
        $id,
        IncidentSource $incidentSource,
        SnapshotSource $snapshotSource
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $snapshotSource->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        /** @var IncidentRecord $incident */
        $incident = $incidentSource->findStoredBySnapshotByPK(
            $snapshot,
            $this->input->query('incident')
        );
        if (empty($incident)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $incident->getExceptionSource();
    }

    /**
     * Remove snapshot incident. Clean source and delete.
     *
     * @param string|int     $id
     * @param IncidentSource $incidentSource
     * @param SnapshotSource $snapshotSource
     * @return array
     */
    public function removeIncidentAction(
        $id,
        IncidentSource $incidentSource,
        SnapshotSource $snapshotSource
    ) {
        /** @var SnapshotRecord $snapshot */
        $snapshot = $snapshotSource->findWithLastByPK($id);
        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        /** @var IncidentRecord $incident */
        $incident = $incidentSource->findBySnapshotByPK(
            $snapshot,
            $this->input->query('incident')
        );
        if (empty($incident)) {
            throw new NotFoundException;
        }

        $this->authorize('edit', compact('snapshot'));

        $incident->delete();

        $uri = $this->vault
            ->uri('snapshots:edit', ['id' => $snapshot->primaryKey()])
            ->withFragment('history');

        if ($this->input->isAjax()) {
            return [
                'status'  => 200,
                'message' => $this->say('Snapshot incident deleted.'),
                'action'  => ['redirect' => $uri]
            ];
        } else {
            return $this->response->redirect($uri);
        }
    }
}