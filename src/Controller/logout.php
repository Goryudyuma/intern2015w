<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

// logout処理
final class logout
{
// session殺してlogoutを返すだけ。
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        $app->session->destroy();

        return new Response\TwigResponse('logout.tpl.html');
    }
}
