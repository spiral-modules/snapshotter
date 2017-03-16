<?php

namespace Spiral\Snapshotter\FileHandler\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\Snapshotter\AbstractController;
use Spiral\Snapshotter\FileHandler\Services\SnapshotService;
use Spiral\Snapshotter\Helpers\Timestamps;
use Spiral\Vault\Vault;
use Spiral\Views\ViewManager;

/**
 * @property InputManager    $input
 * @property ViewManager     $views
 * @property Vault           $vault
 * @property ResponseWrapper $response
 */
class SnapshotsController extends AbstractController
{
    /**
     * List of snapshots.
     *
     * @param SnapshotService $service
     * @param Timestamps      $timestamps
     * @return string
     */
    public function indexAction(SnapshotService $service, Timestamps $timestamps)
    {
        $snapshots = $service->getSnapshots();

        return $this->views->render('snapshotter:file/list', [
            'selector'   => $snapshots->paginate(20),
            'timestamps' => $timestamps
        ]);
    }

    /**
     * View snapshot.
     *
     * @param SnapshotService $service
     * @param Timestamps      $timestamps
     * @return string
     */
    public function viewAction(SnapshotService $service, Timestamps $timestamps)
    {
        $filename = $this->input->input('filename');
        $snapshot = $service->getSnapshot($filename);

        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $this->views->render('snapshotter:file/snapshot', [
            'snapshot'   => $snapshot,
            'timestamps' => $timestamps,
        ]);
    }

    /**
     * View last snapshot incident source.
     *
     * @param SnapshotService $service
     * @return string
     */
    public function iframeAction(SnapshotService $service)
    {
        $filename = $this->input->input('filename');
        $snapshot = $service->getSnapshot($filename);

        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $service->read($snapshot);
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

        $service->deleteSnapshots();

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
     * @param SnapshotService        $service
     * @param ServerRequestInterface $request
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    public function removeAction(SnapshotService $service, ServerRequestInterface $request)
    {
        $filename = $this->input->input('filename');
        $snapshot = $service->getSnapshot($filename);

        if (empty($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('remove', compact('snapshot'));

        $service->deleteSnapshot($snapshot);

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
}