<?php

namespace Spiral\Snapshotter\FileHandler\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
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
     * @return string
     */
    public function indexAction()
    {
        //todo read snapshots list from dir
    }

    /**
     * View snapshot.
     *
     * @param string|int $id
     * @return string
     */
    public function editAction($id)
    {
        //todo open snapshot file
    }

    /**
     * View last snapshot incident source.
     *
     * @param string|int $id
     * @return string
     */
    public function iframeAction($id)
    {
        //todo open snapshot file source
    }

    /**
     * Remove all snapshots with all incident records.
     *
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    public function removeAllAction()
    {
        //todo remove all snapshots
    }

    /**
     * Remove single snapshot with all incident records.
     *
     * @param string|int $id
     * @return array
     */
    public function removeAction($id)
    {
        //todo remove snapshot
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