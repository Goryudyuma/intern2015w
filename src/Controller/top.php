<?php
namespace Nyaan\Controller;
use Nyaan\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

//room一覧を取ってきている
final class top
{
    function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
		//idを取ってくる必要はなさそう・・・
        $stmt = db()->prepare('SELECT * FROM `rooms`');
        $stmt->execute();
        $rooms = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new Response\TemplateResponse('index.tpl.html', [
            'rooms' => $rooms,
        ]);
    }
}
