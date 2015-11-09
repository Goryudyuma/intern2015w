<?php
namespace Nyaan\Controller;
use Nyaan\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

//ユーザーページを取ってくる
final class user
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
		//@を抜いている
        $name = ltrim($action->param['user'], '@');
		//プレースホルダ！
        $query = "SELECT * FROM `users` WHERE `slug` = \"{$name}\"";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new Response\TemplateResponse('user.tpl.html', [
            'user' => $user,
        ]);
    }
}
