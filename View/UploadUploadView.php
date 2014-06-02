<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\View;

class UploadUploadView
{
    /**
     * The form to be displayed.
     *
     * @var \Symfony\Component\Form\FormView
     */
    public $form;

    /**
     * The result, if a file was uploaded. Used only in error contexts where we have to
     * display a failure to the user.
     *
     * @var \Infinite\ImportBundle\Upload\UploadResult
     */
    public $result;
}
