<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\EventListener;

use Infinite\ImportBundle\Import\ImportScheduler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ImportListener implements EventSubscriberInterface
{
    /**
     * @var \Infinite\ImportBundle\Import\ImportScheduler
     */
    private $scheduler;

    public function __construct(ImportScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    public function processImports()
    {
        $this->scheduler->process();
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => 'processImports'
        );
    }
}
