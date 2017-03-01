<?php

namespace Spiral\Snapshotter\FileHandler\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\Snapshotter\FileHandler\Services\SnapshotService;
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
     * @param SnapshotService $service
     * @param Timestamps      $timestamps
     * @param Names           $names
     * @return string
     */
    public function indexAction(SnapshotService $service, Timestamps $timestamps, Names $names)
    {
        $snapshots = $service->getSnapshots();

        return $this->views->render('snapshotter:file/snapshot', [
            'selector'   => $snapshots,
            'timestamps' => $timestamps,
            'names'      => $names
        ]);
    }

    /**
     * View snapshot.
     *
     * @param string          $id
     * @param SnapshotService $service
     * @return string
     */
    public function viewAction($id, SnapshotService $service)
    {
        $snapshot = $id;
        if (!$service->exists($snapshot)) {
            throw new NotFoundException;
        }

        $this->authorize('view', compact('snapshot'));

        return $this->views->render('snapshotter:file/snapshot', [
            'snapshot' => $snapshot
        ]);
    }

    /**
     * View last snapshot incident source.
     *
     * @param string          $id
     * @param SnapshotService $service
     * @return string
     */
    public function iframeAction($id, SnapshotService $service)
    {
        $snapshot = $id;
        if (!$service->exists($snapshot)) {
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
     * @param string                 $id
     * @param SnapshotService        $service
     * @param ServerRequestInterface $request
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    public function removeAction($id, SnapshotService $service, ServerRequestInterface $request)
    {
        $snapshot = $id;
        if (!$service->exists($snapshot)) {
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