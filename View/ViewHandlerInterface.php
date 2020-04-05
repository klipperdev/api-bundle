<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ViewHandlerInterface
{
    /**
     * Handler to format the view into response.
     *
     * @param View         $view    The view
     * @param null|Request $request The request
     */
    public function handle(View $view, ?Request $request = null): Response;
}
