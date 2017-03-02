<?php

namespace Spiral\Snapshotter;

use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Translator\Traits\TranslatorTrait;

abstract class AbstractController extends Controller
{
    use AuthorizesTrait, TranslatorTrait;

    const GUARD_NAMESPACE = 'vault.snapshots';
}